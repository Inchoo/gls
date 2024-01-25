<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    public const CODE = 'gls';

    public const STANDARD_DELIVERY_METHOD    = 'standard';
    public const PARCEL_SHOP_DELIVERY_METHOD = 'psd';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @var \GLSCroatia\Shipping\Model\Api\Service
     */
    protected \GLSCroatia\Shipping\Model\Api\Service $apiService;

    /**
     * @var \GLSCroatia\Shipping\Model\ExpressDelivery\Checker
     */
    protected \GLSCroatia\Shipping\Model\ExpressDelivery\Checker $expressDeliveryChecker;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected \Magento\Framework\App\State $appState;

    /**
     * @var \Magento\Framework\Math\FloatComparator
     */
    protected \Magento\Framework\Math\FloatComparator $floatComparator;

    /**
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     * @param \GLSCroatia\Shipping\Model\Api\Service $apiService
     * @param \GLSCroatia\Shipping\Model\ExpressDelivery\Checker $expressDeliveryChecker
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Math\FloatComparator $floatComparator
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
        \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery,
        \GLSCroatia\Shipping\Model\Api\Service $apiService,
        \GLSCroatia\Shipping\Model\ExpressDelivery\Checker $expressDeliveryChecker,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Math\FloatComparator $floatComparator,
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
        $this->parcelShopDelivery = $parcelShopDelivery;
        $this->apiService = $apiService;
        $this->expressDeliveryChecker = $expressDeliveryChecker;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->appState = $appState;
        $this->floatComparator = $floatComparator;
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
     * @return DataObject|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive() || !$allowedMethods = $this->getValidatedMethods($request)) {
            return false;
        }

        $result = $this->_rateFactory->create();

        foreach ($allowedMethods as $methodCode => $methodTitle) {
            $price = $this->getConfigData("{$methodCode}_method_price") ?: 0;

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
    protected function getValidatedMethods(RateRequest $request): array
    {
        if (!$allowedMethods = $this->getAllowedMethods()) {
            return [];
        }

        $result = [];
        foreach ($allowedMethods as $methodCode => $methodTitle) {
            if ($methodCode === self::PARCEL_SHOP_DELIVERY_METHOD
                && $this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_ADMINHTML
            ) {
                continue; // PSD not available in adminhtml
            }

            $countryAllowSpecific = $this->getConfigData("{$methodCode}_sallowspecific");
            $specificCountry = $this->getConfigData("{$methodCode}_specificcountry");
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
     * @return DataObject
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
        $response = $this->dataObjectFactory->create();

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
     * @return DataObject
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $result = $this->dataObjectFactory->create();

        if (!$clientId = $this->getConfigData('client_id')) {
            return $result->setErrors(__('GLS Client ID is not configured.'));
        }

        $this->_prepareShipmentRequest($request);

        $incrementId = $request->getOrderShipment()->getOrder()->getIncrementId();
        $clientReference = str_replace('{increment_id}', $incrementId, $this->getConfigData('client_reference'));

//        $currentTimestamp = round(microtime(true) * 1000); // milliseconds

        $parcel = [
            'ClientNumber' => (int)$clientId,
            'ClientReference' => $clientReference,
            'Count' => count($request->getPackages() ?: []) ?: 1,
//            'CODAmount',
//            'CODReference',
//            'Content',
//            'PickupDate' => "/Date({$currentTimestamp})/",
            'PickupAddress' => [
                'Name' => $request->getShipperContactCompanyName(),
                'Street' => $request->getShipperAddressStreet(),
//                'HouseNumber',
//                'HouseNumberInfo',
                'City' => $request->getShipperAddressCity(),
                'ZipCode' => $request->getShipperAddressPostalCode(),
                'CountryIsoCode' => $request->getShipperAddressCountryCode(),
                'ContactName' => $request->getShipperContactPersonName(),
                'ContactPhone' => $request->getShipperContactPhoneNumber(),
                'ContactEmail' => $request->getShipperEmail()
            ],
            'DeliveryAddress' => [
                'Name' => $request->getRecipientContactCompanyName() ?: $request->getRecipientContactPersonName(),
                'Street' => $request->getRecipientAddressStreet(),
//                'HouseNumber',
//                'HouseNumberInfo',
                'City' => $request->getRecipientAddressCity(),
                'ZipCode' => $request->getRecipientAddressPostalCode(),
                'CountryIsoCode' => $request->getRecipientAddressCountryCode(),
                'ContactName' => $request->getRecipientContactPersonName(),
                'ContactPhone' => $request->getRecipientContactPhoneNumber(),
                'ContactEmail' => $request->getRecipientEmail()
            ]
        ];

        $customsValue = 0;
        foreach ($request->getPackages() as $packageData) {
            $customsValue += $packageData['params']['customs_value'] ?? 0;
        }

        $countryCallingCode = $this->getCode('country_calling_code', $request->getRecipientAddressCountryCode());
        $recipientPhoneNumber = ltrim((string)$request->getRecipientContactPhoneNumber(), '0');
        if (!str_starts_with($recipientPhoneNumber, (string)$countryCallingCode)) {
            $recipientPhoneNumber = "{$countryCallingCode}{$recipientPhoneNumber}";
        }

        $serviceList = [];

        $isShopDeliveryService = $request->getShippingMethod() === self::PARCEL_SHOP_DELIVERY_METHOD;
        // Parcel Shop Delivery Service
        if ($isShopDeliveryService) {
            $deliveryPointData = $this->parcelShopDelivery->getParcelShopDeliveryPointData(
                $request->getOrderShipment()->getOrder()
            );

            $serviceList[] = [
                'Code' => 'PSD',
                'PSDParameter' => [
                    'StringValue' => $deliveryPointData['id'] ?? ''
                ]
            ];
        }
        // Guaranteed 24h Service
        if ($this->getConfigFlag('guaranteed_24h') && $request->getRecipientAddressCountryCode() !== 'RS') {
            $serviceList[] = [
                'Code' => '24H'
            ];
        }
        // Express Delivery Service
        $expressDeliverCode = $this->getConfigData('express_delivery');
        $isExpressDeliveryAllowed = !$isShopDeliveryService && $expressDeliverCode
            && $this->expressDeliveryChecker->isAllowed(
                $expressDeliverCode,
                $request->getShipperAddressCountryCode(),
                $request->getRecipientAddressCountryCode(),
                (string)$request->getRecipientAddressPostalCode()
            );
        if ($isExpressDeliveryAllowed) {
            $serviceList[] = [
                'Code' => $expressDeliverCode
            ];
        }
        // Contact Service
        if (!$isShopDeliveryService && $this->getConfigFlag('cs1')) {
            $serviceList[] = [
                'Code' => 'CS1',
                'CS1Parameter' => [
                    'Value' => $recipientPhoneNumber
                ]
            ];
        }
        // Flexible Delivery Service
        if (!$isShopDeliveryService && !$this->getConfigFlag('express_delivery') && $this->getConfigFlag('fds')) {
            $serviceList[] = [
                'Code' => 'FDS',
                'FDSParameter' => [
                    'Value' => (string)$request->getRecipientEmail()
                ]
            ];
        }
        // Flexible Delivery SMS Service
        if (!$isShopDeliveryService
            && !$this->getConfigFlag('express_delivery')
            && $this->getConfigFlag('fss')
            && $this->getConfigFlag('fds')
        ) {
            $serviceList[] = [
                'Code' => 'FSS',
                'FSSParameter' => [
                    'Value' => $recipientPhoneNumber
                ]
            ];
        }
        // SMS Service
        if ($this->getConfigFlag('sm1') && $sm1Text = $this->getConfigData('sm1_text')) {
            $serviceList[] = [
                'Code' => 'SM1',
                'SM1Parameter' => [
                    'Value' => "{$recipientPhoneNumber}|$sm1Text"
                ]
            ];
        }
        // SMS Pre-advice Service
        if ($this->getConfigFlag('sm2')) {
            $serviceList[] = [
                'Code' => 'SM2',
                'SM2Parameter' => [
                    'Value' => $recipientPhoneNumber
                ]
            ];
        }
        // Addressee Only Service
        if (!$isShopDeliveryService && $this->getConfigFlag('aos')) {
            $serviceList[] = [
                'Code' => 'AOS',
                'AOSParameter' => [
                    'Value' => (string)$request->getRecipientContactPersonName()
                ]
            ];
        }
        // Insurance Service
        $isInsuranceAllowed = $this->getConfigFlag('ins') && $this->isInsuranceAllowed(
            (float)$customsValue,
            $request->getOrderShipment()->getOrder()->getOrderCurrencyCode(),
            $request->getShipperAddressCountryCode(),
            $request->getRecipientAddressCountryCode()
        );
        if ($isInsuranceAllowed) {
            $serviceList[] = [
                'Code' => 'INS',
                'INSParameter' => [
                    'Value' => $customsValue
                ]
            ];
        }

        if ($serviceList) {
            $parcel['ServiceList'] = $serviceList;
        }

        $params = [
            'ParcelList' => [$parcel],
            'TypeOfPrinter' => $this->getConfigData('printer_type'),
            'PrintPosition' => (int)$this->getConfigData('print_position') ?: 1,
            'ShowPrintDialog' => false
        ];

        $response = $this->apiService->printLabels($params);
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
    public function getTracking(string $trackingNumber): Result
    {
        $countryCode = $this->getConfigData('api_country');

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
        $tracking->setUrl("https://gls-group.eu/{$countryCode}/en/parcel-tracking/?match={$trackingNumber}");

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
     * Check if insurance is allowed for package value.
     *
     * @param float $packageValue
     * @param string $packageValueCurrency
     * @param string $originCountry
     * @param string $destinationCountry
     * @return bool
     */
    protected function isInsuranceAllowed(
        float $packageValue,
        string $packageValueCurrency,
        string $originCountry,
        string $destinationCountry
    ): bool {
        if (!$originCurrencyCode = $this->getCode('country_currency_code', $originCountry)) {
            return false;
        }

        $type = $originCountry === $destinationCountry ? 'country_domestic_insurance' : 'country_export_insurance';
        if (!$minMax = $this->getCode($type, $originCountry)) {
            return false;
        }

        try {
            $currencyModel = $this->_currencyFactory->create()->load($packageValueCurrency);
            $packageValue = (float)$currencyModel->convert($packageValue, $originCurrencyCode);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->error('GLS insurance check.', [
                'message' => $e->getMessage()
            ]);
            return false;
        }

        return $this->floatComparator->greaterThanOrEqual($packageValue, (float)$minMax['min'])
            && $this->floatComparator->greaterThanOrEqual((float)$minMax['max'], $packageValue);
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
        $data = [
            'method' => [
                self::STANDARD_DELIVERY_METHOD => __('Delivery to Address'),
                self::PARCEL_SHOP_DELIVERY_METHOD => __('Delivery to Parcel Location')
            ],
            'country_calling_code' => [
                'CZ' => '+420',
                'HR' => '+385',
                'HU' => '+36',
                'RO' => '+40',
                'SI' => '+386',
                'SK' => '+421',
                'RS' => '+381',
            ],
            'country_currency_code' => [
                'CZ' => 'CZK',
                'HR' => 'EUR',
                'HU' => 'HUF',
                'RO' => 'RON',
                'SI' => 'EUR',
                'SK' => 'EUR',
                'RS' => 'RSD',
            ],
            'country_domestic_insurance' => [
                'CZ' => ['min' => 20000, 'max' => 100000], // CZK
                'HR' => ['min' => 165.9, 'max' => 1659.04], // EUR
                'HU' => ['min' => 50000, 'max' => 500000], // HUF
                'RO' => ['min' => 2000, 'max' => 7000], // RON
                'SI' => ['min' => 200, 'max' => 2000], // EUR
                'SK' => ['min' => 332, 'max' => 2655], // EUR
                'RS' => ['min' => 40000, 'max' => 200000] // RSD
            ],
            'country_export_insurance' => [
                'CZ' => ['min' => 20000, 'max' => 100000], // CZK
                'HR' => ['min' => 165.91, 'max' => 663.61], // EUR
                'HU' => ['min' => 50000, 'max' => 200000], // HUF
                'RO' => ['min' => 2000, 'max' => 7000], // RON
                'SI' => ['min' => 200, 'max' => 2000], // EUR
                'SK' => ['min' => 332, 'max' => 1000] // EUR
            ]
        ];

        if ($code === null) {
            return $data[$type] ?? [];
        }

        return $data[$type][$code] ?? null;
    }
}
