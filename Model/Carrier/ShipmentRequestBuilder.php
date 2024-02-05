<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier;

use Magento\Framework\DataObject;

class ShipmentRequestBuilder
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @var \GLSCroatia\Shipping\Model\ExpressDelivery\Checker
     */
    protected \GLSCroatia\Shipping\Model\ExpressDelivery\Checker $expressDeliveryChecker;

    /**
     * @var \Magento\Framework\Math\FloatComparator
     */
    protected \Magento\Framework\Math\FloatComparator $floatComparator;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected \Magento\Directory\Model\CurrencyFactory $currencyFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     * @param \GLSCroatia\Shipping\Model\ExpressDelivery\Checker $expressDeliveryChecker
     * @param \Magento\Framework\Math\FloatComparator $floatComparator
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery,
        \GLSCroatia\Shipping\Model\ExpressDelivery\Checker $expressDeliveryChecker,
        \Magento\Framework\Math\FloatComparator $floatComparator,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->parcelShopDelivery = $parcelShopDelivery;
        $this->expressDeliveryChecker = $expressDeliveryChecker;
        $this->floatComparator = $floatComparator;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Get shipment request API params.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParams(DataObject $request): array
    {
        $storeId = $request->getStoreId();

        if (!$clientId = $this->config->getClientId($storeId)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('GLS Client ID is not configured.')); // todo test this
        }

        $incrementId = $request->getOrderShipment()->getOrder()->getIncrementId();
        $clientReference = str_replace('{increment_id}', $incrementId, $this->config->getClientReference($storeId));

//        $currentTimestamp = round(microtime(true) * 1000); // milliseconds

        $parcel = [
            'ClientNumber' => (int)$clientId,
            'ClientReference' => $clientReference,
            'Count' => count($request->getPackages() ?: []) ?: 1,
//            'CODAmount',todo
//            'CODReference',todo
//            'Content',todo
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

        $recipientPhoneNumber = $this->formatPhoneNumber(
            (string)$request->getRecipientContactPhoneNumber(),
            (string)$request->getRecipientAddressCountryCode()
        );

        $serviceList = [];
        $isShopDeliveryService = $this->isShopDeliveryService((string)$request->getShippingMethod());

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
        if ($this->config->isEnabledGuaranteed24hService($storeId)
            && $request->getRecipientAddressCountryCode() !== 'RS'
        ) {
            $serviceList[] = [
                'Code' => '24H'
            ];
        }

        // Express Delivery Service
        $expressDeliverCode = $this->config->getExpressDeliveryServiceCode($storeId);
        $isExpressDeliveryAllowed = !$isShopDeliveryService
            && $expressDeliverCode
            && $this->expressDeliveryChecker->isAllowed(
                $expressDeliverCode,
                (string)$request->getShipperAddressCountryCode(),
                (string)$request->getRecipientAddressCountryCode(),
                (string)$request->getRecipientAddressPostalCode()
            );
        if ($isExpressDeliveryAllowed) {
            $serviceList[] = [
                'Code' => $expressDeliverCode
            ];
        }

        // Contact Service
        if (!$isShopDeliveryService
            && $this->config->isEnabledContactService($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'CS1',
                'CS1Parameter' => [
                    'Value' => $recipientPhoneNumber
                ]
            ];
        }

        // Flexible Delivery Service
        if (!$isShopDeliveryService
            && !$isExpressDeliveryAllowed
            && $this->config->isEnabledFlexibleDeliveryService($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'FDS',
                'FDSParameter' => [
                    'Value' => (string)$request->getRecipientEmail()
                ]
            ];
        }

        // Flexible Delivery SMS Service
        if (!$isShopDeliveryService
            && !$isExpressDeliveryAllowed
            && $this->config->isEnabledFlexibleDeliveryService($storeId)
            && $this->config->isEnabledFlexibleDeliverySmsService($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'FSS',
                'FSSParameter' => [
                    'Value' => $recipientPhoneNumber
                ]
            ];
        }

        // SMS Service
        if ($this->config->isEnabledSmsService($storeId)
            && $sm1Text = $this->config->getSmsServiceText($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'SM1',
                'SM1Parameter' => [
                    'Value' => "{$recipientPhoneNumber}|$sm1Text"
                ]
            ];
        }
        // SMS Pre-advice Service
        if ($this->config->isEnabledSmsPreAdviceService($storeId)) {
            $serviceList[] = [
                'Code' => 'SM2',
                'SM2Parameter' => [
                    'Value' => $recipientPhoneNumber
                ]
            ];
        }

        // Addressee Only Service
        if (!$isShopDeliveryService && $this->config->isEnabledAddresseeOnlyService($storeId)) {
            $serviceList[] = [
                'Code' => 'AOS',
                'AOSParameter' => [
                    'Value' => (string)$request->getRecipientContactPersonName()
                ]
            ];
        }

        // Insurance Service
        $isInsuranceAllowed = $this->config->isEnabledInsuranceService($storeId)
            && $this->isInsuranceAllowed(
                (float)$customsValue,
                (string)$request->getOrderShipment()->getOrder()->getOrderCurrencyCode(),
                (string)$request->getShipperAddressCountryCode(),
                (string)$request->getRecipientAddressCountryCode()
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

        return [
            'ParcelList' => [$parcel],
            'TypeOfPrinter' => $this->config->getPrinterType($storeId),
            'PrintPosition' => $this->config->getPrintPosition($storeId),
            'ShowPrintDialog' => false
        ];
    }

    /**
     * Check if it's PSD shipping option.
     *
     * @param string $shippingMethod
     * @return bool
     */
    protected function isShopDeliveryService(string $shippingMethod): bool
    {
        return $shippingMethod === \GLSCroatia\Shipping\Model\Carrier::PARCEL_SHOP_DELIVERY_METHOD;
    }

    /**
     * Prepend the phone number with the country calling code.
     *
     * @param string $phoneNumber
     * @param string $countryCode
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber, string $countryCode): string
    {
        $countryCallingCode = (string)$this->dataHelper->getConfigCode('country_calling_code', $countryCode);
        if (!$countryCallingCode) {
            return $phoneNumber;
        }

        $tempPhoneNumber = ltrim($phoneNumber, '0');
        if (strpos($tempPhoneNumber, $countryCallingCode) !== 0) {
            $phoneNumber = "{$countryCallingCode}{$tempPhoneNumber}";
        }

        return $phoneNumber;
    }

    /**
     * Check if insurance is allowed for package value.
     *
     * @param float $packageValue
     * @param string $packageValueCurrency
     * @param string $originCountry
     * @param string $destinationCountry
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isInsuranceAllowed(
        float $packageValue,
        string $packageValueCurrency,
        string $originCountry,
        string $destinationCountry
    ): bool {
        if (!$originCurrencyCode = $this->dataHelper->getConfigCode('country_currency_code', $originCountry)) {
            return false;
        }

        $type = $originCountry === $destinationCountry ? 'country_domestic_insurance' : 'country_export_insurance';
        if (!$minMax = $this->dataHelper->getConfigCode($type, $originCountry)) {
            return false;
        }

        $currencyModel = $this->currencyFactory->create()->load($packageValueCurrency);
        $packageValue = (float)$currencyModel->convert($packageValue, $originCurrencyCode);

        return $this->floatComparator->greaterThanOrEqual($packageValue, (float)$minMax['min'])
            && $this->floatComparator->greaterThanOrEqual((float)$minMax['max'], $packageValue);
    }
}
