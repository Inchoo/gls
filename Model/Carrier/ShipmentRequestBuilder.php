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
use Magento\Sales\Model\Order\Shipment;

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
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\CashOnDelivery
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\CashOnDelivery $cashOnDelivery;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\ExpressDelivery
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\ExpressDelivery $expressDelivery;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Insurance
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Insurance $insurance;

    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\CashOnDelivery $cashOnDelivery
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\ExpressDelivery $expressDelivery
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Insurance $insurance
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\CashOnDelivery $cashOnDelivery,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\ExpressDelivery $expressDelivery,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Insurance $insurance,
        \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
    ) {
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->cashOnDelivery = $cashOnDelivery;
        $this->expressDelivery = $expressDelivery;
        $this->insurance = $insurance;
        $this->parcelShopDelivery = $parcelShopDelivery;
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
            throw new \Magento\Framework\Exception\LocalizedException(__('GLS Client ID is not configured.'));
        }

        $shipment = $request->getOrderShipment();
        $order = $shipment->getOrder();

//        $currentTimestamp = round(microtime(true) * 1000); // milliseconds

        $parcel = [
            'ClientNumber' => (int)$clientId,
            'ClientReference' => $this->generateReference($shipment),
            'Count' => count($request->getPackages() ?: []) ?: 1,
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

        if ($request->getRecipientAddressCountryCode() === 'RS') {
            $parcel['SenderIdentityCardNumber'] = $this->config->getSenderIdentityCardNumber($storeId);
            $parcel['Content'] = $this->config->getContent($storeId);
        }

        $recipientPhoneNumber = $this->formatPhoneNumber(
            (string)$request->getRecipientContactPhoneNumber(),
            (string)$request->getRecipientAddressCountryCode()
        );

        $serviceList = [];

        // Cash on Delivery Service
        if ($this->cashOnDelivery->isAllowed($shipment)) {
            $parcel['CODAmount'] = $this->cashOnDelivery->calculateAmount($shipment);
            $parcel['CODReference'] = $this->cashOnDelivery->generateReference($shipment);
            $serviceList[] = ['Code' => 'COD'];
        }

        // Parcel Shop Delivery Service
        $isShopDeliveryService = $this->isShopDeliveryService((string)$request->getShippingMethod());
        if ($isShopDeliveryService) {
            $deliveryPointData = $this->parcelShopDelivery->getParcelShopDeliveryPointData($order);

            $serviceList[] = [
                'Code' => 'PSD',
                'PSDParameter' => ['StringValue' => (string)$deliveryPointData->getData('id')]
            ];
        }

        // Guaranteed 24h Service
        if ($this->config->isEnabledGuaranteed24hService($storeId)
            && $request->getRecipientAddressCountryCode() !== 'RS'
        ) {
            $serviceList[] = ['Code' => '24H'];
        }

        // Express Delivery Service
        $expressDeliverCode = $this->config->getExpressDeliveryServiceCode($storeId);
        $isExpressDeliveryAllowed = !$isShopDeliveryService
            && $expressDeliverCode
            && $this->expressDelivery->isAllowed(
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
                'CS1Parameter' => ['Value' => $recipientPhoneNumber]
            ];
        }

        // Flexible Delivery Service
        if (!$isShopDeliveryService
            && !$isExpressDeliveryAllowed
            && $this->config->isEnabledFlexibleDeliveryService($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'FDS',
                'FDSParameter' => ['Value' => (string)$request->getRecipientEmail()]
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
                'FSSParameter' => ['Value' => $recipientPhoneNumber]
            ];
        }

        // SMS Service
        if ($this->config->isEnabledSmsService($storeId)
            && $sm1Text = $this->config->getSmsServiceText($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'SM1',
                'SM1Parameter' => ['Value' => "{$recipientPhoneNumber}|$sm1Text"]
            ];
        }
        // SMS Pre-advice Service
        if ($this->config->isEnabledSmsPreAdviceService($storeId)) {
            $serviceList[] = [
                'Code' => 'SM2',
                'SM2Parameter' => ['Value' => $recipientPhoneNumber]
            ];
        }

        // Addressee Only Service
        if (!$isShopDeliveryService
            && $this->config->isEnabledAddresseeOnlyService($storeId)
        ) {
            $serviceList[] = [
                'Code' => 'AOS',
                'AOSParameter' => ['Value' => (string)$request->getRecipientContactPersonName()]
            ];
        }

        // Insurance Service
        $isInsuranceAllowed = $this->config->isEnabledInsuranceService($storeId)
            && $this->insurance->isAllowed(
                $shipment,
                (string)$request->getShipperAddressCountryCode(),
                (string)$request->getRecipientAddressCountryCode()
            );
        if ($isInsuranceAllowed) {
            $serviceList[] = [
                'Code' => 'INS',
                'INSParameter' => ['Value' => $this->insurance->calculateValue($shipment)]
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
        return $this->dataHelper->isLockerShopDeliveryMethod($shippingMethod);
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
     * Generate "ClientReference" value.
     *
     * @param Shipment $shipment
     * @return string
     */
    protected function generateReference(Shipment $shipment): string
    {
        return str_replace(
            '{increment_id}',
            (string)$shipment->getOrder()->getIncrementId(),
            $this->config->getClientReference($shipment->getStoreId())
        );
    }
}
