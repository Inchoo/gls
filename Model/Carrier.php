<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    public const CODE = 'gls';

    public const HOME_DELIVERY_METHOD        = 'home';
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
     * @var \Magento\Framework\DataObjectFactory
     */
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    /**
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     * @param \GLSCroatia\Shipping\Model\Api\Service $apiService
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
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
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
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
        $this->dataObjectFactory = $dataObjectFactory;
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
        if (!$this->isActive() || !$allowedMethods = $this->getAllowedMethods()) {
            return false;
        }

        $result = $this->_rateFactory->create();

        foreach ($allowedMethods as $methodCode => $methodTitle) {
            $method = $this->_rateMethodFactory->create();

            $price = $this->getConfigData("{$methodCode}_method_price") ?: 0;

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
     * @return \Magento\Framework\DataObject
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

        $parcel = [
            'ClientNumber' => (int)$clientId,
            'ClientReference' => $clientReference,
            'Count' => count($request->getPackages() ?: []) ?: 1,
//            'CODAmount',
//            'CODReference',
//            'Content',
//            'PickupDate',
            'PickupAddress' => [
                'Name' => $request->getShipperContactCompanyName(),
                'Street' => $request->getShipperAddressStreet1(), // todo check
//                'HouseNumber',
//                'HouseNumberInfo',
                'City' => $request->getShipperAddressCity(),
                'ZipCode' => $request->getShipperAddressPostalCode(),
                'CountryIsoCode' => $request->getShipperAddressCountryCode(),
                'ContactName' => $request->getShipperContactPersonName()
            ],
            'DeliveryAddress' => [
                'Name' => $request->getRecipientContactCompanyName() ?: $request->getRecipientContactPersonName(),
                'Street' => $request->getRecipientAddressStreet1(),
//                'HouseNumber',
//                'HouseNumberInfo',
                'City' => $request->getRecipientAddressCity(),
                'ZipCode' => $request->getRecipientAddressPostalCode(),
                'CountryIsoCode' => $request->getRecipientAddressCountryCode(),
                'ContactName' => $request->getRecipientContactPersonName()
            ]
        ];

        $customsValue = 0;
        foreach ($request->getPackages() as $packageData) {
            $customsValue += $packageData['params']['customs_value'] ?? 0; // todo test with multiple qty
        }

        $serviceList = [];

        if ($request->getShippingMethod() === self::PARCEL_SHOP_DELIVERY_METHOD) {
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
        if ($this->getConfigFlag('service/guaranteed_24h')) {
            $serviceList[] = [
                'Code' => '24H'
            ];
        }
        if ($this->getConfigFlag('service/aos')) {
            $serviceList[] = [
                'Code' => 'AOS',
                'AOSParameter' => [
                    'Value' => $request->getRecipientContactPersonName()
                ]
            ];
        }
        if ($this->getConfigFlag('service/cs1')) {
            $serviceList[] = [
                'Code' => 'CS1',
                'CS1Parameter' => [
                    'Value' => $request->getRecipientContactPhoneNumber()
                ]
            ];
        }
        if ($this->getConfigFlag('service/fds')) {
            $serviceList[] = [
                'Code' => 'FDS',
                'FDSParameter' => [
                    'Value' => $request->getRecipientEmail()
                ]
            ];
        }
        if ($this->getConfigFlag('service/fss') && $this->getConfigFlag('service/fds')) {
            $serviceList[] = [
                'Code' => 'FSS',
                'FSSParameter' => [
                    'Value' => (string)$request->getRecipientContactPhoneNumber() // todo international format
                ]
            ];
        }
        if ($this->getConfigFlag('service/ins')) {
            $serviceList[] = [
                'Code' => 'INS',
                'INSParameter' => [
                    'Value' => $customsValue
                ]
            ];
        }
        if ($this->getConfigFlag('service/sm1') && $sm1Text = $this->getConfigData('service/sm1_text')) {
            $serviceList[] = [
                'Code' => 'SM1',
                'SM1Parameter' => [
                    'Value' => "{$request->getRecipientContactPhoneNumber()}|$sm1Text" // todo international format
                ]
            ];
        }
        if ($this->getConfigFlag('service/sm2')) {
            $serviceList[] = [
                'Code' => 'SM2',
                'SM2Parameter' => [
                    'Value' => (string)$request->getRecipientContactPhoneNumber() // todo international format
                ]
            ];
        }
        if ($this->getConfigFlag('service/tgs')) {
            $serviceList[] = [
                'Code' => 'TGS'
            ];
        }

        $params = [
            'ParcelList' => [$parcel],
            'TypeOfPrinter' => $this->getConfigData('printer_type'),
            'PrintPosition' => (int)$this->getConfigData('print_position') ?: 1,
            'ShowPrintDialog' => false,
            'ServiceList' => $serviceList
        ];

        $response = $this->apiService->printLabels($params); // todo log
        $body = $response->getDecodedBody();

        if ($printLabelsErrorList = $body['PrintLabelsErrorList'] ?? []) {
            // todo log
            return $result->setErrors($printLabelsErrorList[0]['ErrorDescription'] ?? __('GLS API error.'));
        }

        if ($labels = $body['Labels'] ?? []) {
            $result->setShippingLabelContent(implode(array_map('chr', $labels)));
        }
        if (!$result->getShippingLabelContent()) {
            // todo log
            return $result->setErrors(__('Could not create label.'));
        }

        if ($printLabelsInfoList = $body['PrintLabelsInfoList'] ?? []) {
            $result->setTrackingNumber('tracking_number', $printLabelsInfoList[0]['ParcelNumber'] ?? null);
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
    public function getCode(string $type, int|string $code = null): mixed
    {
        $data = [
            'method' => [
                self::HOME_DELIVERY_METHOD => __('Home Delivery'),
                self::PARCEL_SHOP_DELIVERY_METHOD => __('Out of Home Delivery')
            ]
        ];

        if ($code === null) {
            return $data[$type] ?? [];
        }

        return $data[$type][$code] ?? null;
    }
}
