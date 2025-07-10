<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    public const CODE = 'gls';

    public const STANDARD_DELIVERY_METHOD      = 'standard';
    public const PARCEL_LOCKER_DELIVERY_METHOD = 'locker';
    public const PARCEL_SHOP_DELIVERY_METHOD   = 'shop';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier\Context
     */
    protected \GLSCroatia\Shipping\Model\Carrier\Context $context;

    /**
     * @param \GLSCroatia\Shipping\Model\Carrier\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Carrier\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->context = $context;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Collect and get GLS rates.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Framework\DataObject|bool|null
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->isActive() || !$allowedMethods = $this->getValidatedMethods($request)) {
            return false;
        }

        $packageValue = $request->getPackageValue();
        $packageValueWithDiscount = $request->getPackageValueWithDiscount();

        // exclude virtual products from package value
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $packageValue -= $child->getBaseRowTotal();
                            $packageValueWithDiscount -= $child->getBaseRowTotal() - $child->getBaseDiscountAmount();
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $packageValue -= $item->getBaseRowTotal();
                    $packageValueWithDiscount -= $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
                }
            }
        }

        $excludeDiscount = $this->getConfigFlag('exclude_discount_amount');
        $result = $this->_rateFactory->create();

        foreach ($allowedMethods as $methodCode => $methodTitle) {
            $packageSubtotal = $excludeDiscount ? $packageValueWithDiscount : $packageValue;

            if ($methodCode === static::STANDARD_DELIVERY_METHOD) {
                $request->setData('gls_package_subtotal', $packageSubtotal);
                $request->setData('gls_package_weight', $request->getPackageWeight());
                $request->setData('gls_package_qty', $request->getPackageQty());
                $request->setData('gls_conditions', ['weight', 'subtotal', 'quantity']);

                $rate = $this->context->getTablerateResource()->getRate($request);

                $price = $rate ? (float)$rate['price'] : $this->getConfigData("{$methodCode}_method_price");
            } else {
                $price = $this->getConfigData("{$methodCode}_method_price");
            }

            // free shipping
            if ($this->isFreeShipping($methodCode, $packageSubtotal)) {
                $price = 0;
            }

            if ($price === null || $price === false || $price === '') {
                continue; // skip the method without a price
            }

            $method = $this->_rateMethodFactory->create();
            $method->setData([
                'carrier'       => $this->getCarrierCode(),
                'carrier_title' => $this->getConfigData('title'),
                'method'        => $methodCode,
                'method_title'  => $this->getConfigData("{$methodCode}_method_name") ?: $methodTitle,
                'price'         => (float)$price,
                'cost'          => (float)$price
            ]);

            $result->append($method);
        }

        return $result;
    }

    /**
     * Validate allowed methods with shipping request.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array
     */
    protected function getValidatedMethods(\Magento\Quote\Model\Quote\Address\RateRequest $request): array
    {
        if (!$allowedMethods = $this->getAllowedMethods()) {
            return [];
        }

        $result = [];
        foreach ($allowedMethods as $methodCode => $methodTitle) {
            if ($this->context->getDataHelper()->isLockerShopDeliveryMethod($methodCode)
                && $this->context->getAppState()->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML
            ) {
                continue; // PSD not available in adminhtml
            }

            $countryAllowSpecific = $this->getConfigData("{$methodCode}_method_sallowspecific");
            $specificCountry = $this->getConfigData("{$methodCode}_method_specificcountry");
            $availableCountries = $specificCountry ? explode(',', (string)$specificCountry) : [];

            if ($countryAllowSpecific && $countryAllowSpecific == 1
                && !in_array($request->getDestCountryId(), $availableCountries, true)
            ) {
                continue; // not available for destination country
            }

            $result[$methodCode] = $methodTitle;
        }

        return $result;
    }

    /**
     * Get allowed GLS shipping methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));

        $result = [];
        foreach ($allowedMethods as $code) {
            $result[$code] = $this->getCode('method', $code) ?: 'GLS';
        }

        return $result;
    }

    /**
     * Do request to shipment.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }

        if ($request->getStoreId() !== null) {
            $this->setStore($request->getStoreId());
        }

        $result = $this->_doShipmentRequest($request);
        $response = $this->context->getDataObjectFactory()->create();

        $data = [];
        if ($errors = $result->getErrors()) {
            $response->setErrors($errors);
            $this->rollBack($data);
        } else {
            $data[] = [
                'tracking_number' => $result->getTrackingNumber(),
                'label_content' => $result->getShippingLabelContent(),
            ];
        }

        $response->setData('info', $data);

        return $response;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);

        $result = $this->context->getDataObjectFactory()->create();

        try {
            $params = $this->context->getShipmentRequestBuilder()->getParams($request);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $result->setErrors($e->getMessage());
        }

        $response = $this->context->getApiService()->printLabels($params);
        $body = $response->getDecodedBody();

        if ($printLabelsErrorList = $body['PrintLabelsErrorList'] ?? []) {
            return $result->setErrors($printLabelsErrorList[0]['ErrorDescription'] ?? __('GLS API error.'));
        }

        if ($labels = $body['Labels'] ?? []) {
            $result->setShippingLabelContent(implode(array_map('chr', $labels)));
        }
        if (!$result->getShippingLabelContent()) {
            return $result->setErrors(__('Could not create label.'));
        }

        if ($printLabelsInfoList = $body['PrintLabelsInfoList'] ?? []) {
            $result->setTrackingNumber($printLabelsInfoList[0]['ParcelNumber'] ?? null);
        }

        return $result;
    }

    /**
     * Generate tracking info.
     *
     * @param string $trackingNumber
     * @return \Magento\Shipping\Model\Tracking\Result
     */
    public function getTracking(string $trackingNumber): \Magento\Shipping\Model\Tracking\Result
    {
        $countryCode = $this->context->getConfig()->getApiCountryCode($this->getStore());

//        $response = $this->apiService->getParcelStatuses([
//            'ParcelNumber' => $trackingNumber,
//            'ReturnPOD' => false,
//            'LanguageIsoCode' => $countryCode
//        ]);
//        $body = $response->getDecodedBody();

        $tracking = $this->_trackStatusFactory->create();
        $tracking->setCarrier($this->getCarrierCode());
        $tracking->setCarrierTitle($this->getConfigData('title'));
        $tracking->setTracking($trackingNumber);
        $tracking->setUrl($this->context->getDataHelper()->generateTrackingUrl($trackingNumber, $countryCode));

//        $tracking->setTrackSummary(null);
//        $tracking->setStatus(null);
//        $tracking->setSignedBy(null);
//        $tracking->setDeliveryLocation(null);
//        $tracking->setShippedDate(null);
//        $tracking->setDeliveryDate(null);
//        $tracking->setService(null);
//        $tracking->setWeight(null);
//        $tracking->setProgressdetail([]);

        $result = $this->_trackFactory->create();
        $result->append($tracking);

        return $result;
    }

    /**
     * Zip code is not required.
     *
     * @param string|null $countryId
     * @return false
     */
    public function isZipCodeRequired($countryId = null)
    {
        return false;
    }

    /**
     * Get GLS carrier configuration data.
     *
     * @param string $type
     * @param int|string|null $code
     * @return mixed
     */
    public function getCode(string $type, $code = null)
    {
        return $this->context->getDataHelper()->getConfigCode($type, $code);
    }

    /**
     * Check if free shipping is available.
     *
     * @param string $methodCode
     * @param float $packageSubtotal
     * @return bool
     */
    protected function isFreeShipping(string $methodCode, float $packageSubtotal): bool
    {
        if (!$this->getConfigFlag('free_shipping_enabled')) {
            return false;
        }

        $freeShippingThreshold = (float)$this->getConfigData('free_shipping_threshold');
        $freeShippingMethods = $this->getConfigData('free_shipping_methods');
        $freeShippingMethods = $freeShippingMethods ? explode(',', $freeShippingMethods) : [];

        return $freeShippingThreshold > 0
            && $packageSubtotal >= $freeShippingThreshold
            && in_array($methodCode, $freeShippingMethods, true);
    }
}
