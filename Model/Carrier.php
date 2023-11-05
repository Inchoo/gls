<?php

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
            ],
            'status_code' => [
                1 => __('The parcel was handed over to GLS.'),
                2 => __('The parcel has left the parcel center.'),
                3 => __('The parcel has reached the parcel center.'),
                4 => __('The parcel is expected to be delivered during the day.'),
                5 => __('The parcel has been delivered.'),
                6 => __('The parcel is stored in the parcel center.'),
                7 => __('The parcel is stored in the parcel center.'),
                8 => __('The parcel is stored in the GLS parcel center. The consignee has agreed to collect the goods himself.'), // phpcs:ignore
                9 => __('The parcel is stored in the parcel center to be delivered at a new delivery date.'),
                10 => __('Check scan normal'),
                11 => __('The parcel could not be delivered as the consignee is on holidays.'),
                12 => __('The parcel could not be delivered as the consignee was absent.'),
                13 => __('Sorting error at the depot.'),
                14 => __('The parcel could not be delivered as the reception was closed.'),
                15 => __('Not delivered lack of time'),
                16 => __('The parcel could not be delivered as the consignee had no cash available/suitable.'),
                17 => __('The parcel could not be delivered as the recipient refused acceptance.'),
                18 => __('The parcel could not be delivered as further address information is needed.'),
                19 => __('The parcel could not be delivered due to the weather condition.'),
                20 => __('The parcel could not be delivered due to wrong or incomplete address.'),
                21 => __('Forwarded sorting error'),
                22 => __('Parcel is sent from the depot to sorting center.'),
                23 => __('The parcel has been returned to sender.'),
                24 => __('The changed delivery option has been saved in the GLS system and will be implemented as requested.'), // phpcs:ignore
                25 => __('Forwarded misrouted'),
                26 => __('The parcel has reached the parcel center.'),
                27 => __('The parcel has reached the parcel center.'),
                28 => __('Disposed'),
                29 => __('Parcel is under investigation.'),
                30 => __('Inbound damaged'),
                31 => __('Parcel was completely damaged.'),
                32 => __('The parcel will be delivered in the evening.'),
                33 => __('The parcel could not be delivered due to exceeded time frame.'),
                34 => __('The parcel could not be delivered as acceptance has been refused due to delayed delivery.'),
                35 => __('Parcel was refused because the goods was not ordered.'),
                36 => __('Consignee was not in, contact card couldn ́t be left.'),
                37 => __('Change delivery for shipper ́s request.'),
                38 => __('The parcel could not be delivered due to missing delivery note.'),
                39 => __('Delivery note not signed'),
                40 => __('The parcel has been returned to sender.'),
                41 => __('Forwarded normal'),
                42 => __('The parcel was disposed upon shipper ́s request.'),
                43 => __('Parcel is not to locate.'),
                44 => __('Parcel is excluded from General Terms and Conditions.'),
                46 => __('Change completed for Delivery address'),
                47 => __('The parcel has left the parcel center.'),
                51 => __('The parcel data was entered into the GLS IT system; the parcel was not yet handed over to GLS.'), // phpcs:ignore
                52 => __('The COD data was entered into the GLS IT system.'),
                54 => __('The parcel has been delivered to the parcel box.'),
                55 => __('The parcel has been delivered at the ParcelShop (see ParcelShop information).'),
                56 => __('Parcel is stored in GLS ParcelShop.'),
                57 => __('The parcel has reached the maximum storage time in the ParcelShop.'),
                58 => __('The parcel has been delivered at the neighbour’s (see signature)'),
                60 => __('Customs clearance is delayed due to a missing invoice.'),
                61 => __('The customs documents are being prepared.'),
                62 => __('Customs clearance is delayed as the consignee ́s phone number is not available.'),
                64 => __('The parcel was released by customs.'),
                65 => __('The parcel was released by customs. Customs clearance is carried out by the consignee.'),
                66 => __('Customs clearance is delayed until the consignee ́s approval is available.'),
                67 => __('The customs documents are being prepared.'),
                68 => __('The parcel could not be delivered as the consignee refused to pay charges.'),
                69 => __('The parcel is stored in the parcel center. It cannot be delivered as the consignment is not complete.'), // phpcs:ignore
                70 => __('Customs clearance is delayed due to incomplete documents.'),
                71 => __('Customs clearance is delayed due to missing or inaccurate customs documents.'),
                72 => __('Customs data must be recorded.'),
                73 => __('Customs parcel locked in origin country.'),
                74 => __('Customs clearance is delayed due to a customs inspection.'),
                75 => __('Parcel was confiscated by the Customs authorities.'),
                76 => __('Customs data recorded, parcel can be sent do final location.'),
                80 => __('The parcel has been forwarded to the desired address to be delivered there.'),
                83 => __('The parcel data for Pickup-Service was entered into the GLS system.'),
                84 => __('The parcel label for the pickup has been produced.'),
                85 => __('The driver has received the order to pick up the parcel during the day.'),
                86 => __('The parcel has reached the parcel center.'),
                87 => __('The pickup request has been cancelled as there were no goods to be picked up.'),
                88 => __('The parcel could not be picked up as the goods to be picked up were not packed.'),
                89 => __('The parcel could not be picked up as the customer was not informed about the pickup.'),
                90 => __('The pickup request has been cancelled as the goods were sent by other means.'),
                91 => __('Pick and Ship/Return cancelled'),
                92 => __('The parcel has been delivered.'),
                93 => __('Signature confirmed'),
                99 => __('Consignee contacted Email delivery notification')
            ]
        ];

        if ($code === null) {
            return $data[$type] ?? [];
        }

        return $data[$type][$code] ?? null;
    }
}
