<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier;

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
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service $service;

    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service $service
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service $service,
        \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
    ) {
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->service = $service;
        $this->parcelShopDelivery = $parcelShopDelivery;
    }

    /**
     * Get shipment request API params.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParams(\Magento\Framework\DataObject $request): array
    {
        $storeId = $request->getStoreId();

        if (!$clientId = $this->config->getClientId($storeId)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('GLS Client ID is not configured.'));
        }

        $shipment = $request->getOrderShipment();
        $requestData = $request->getData('gls') ?: [];

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
        if ($this->service->isCashOnDeliveryAllowed($request)) {
            $codReference = $requestData['cod_reference'] ?? $this->service->getCashOnDeliveryReference($request);

            $parcel['CODAmount'] = $this->service->getCashOnDeliveryAmount($request);
            $parcel['CODReference'] = $codReference;
            $serviceList[] = ['Code' => 'COD'];
        }

        // Parcel Shop Delivery Service
        if ($this->isShopDeliveryService((string)$request->getShippingMethod())) {
            $deliveryPointData = $this->parcelShopDelivery->getParcelShopDeliveryPointData($shipment->getOrder());

            $serviceList[] = [
                'Code' => 'PSD',
                'PSDParameter' => ['StringValue' => (string)$deliveryPointData->getData('id')]
            ];
        }

        // Guaranteed 24h Service
        $isEnabledGuaranteed24h = $requestData['guaranteed_24h'] ?? $this->config->isEnabledGuaranteed24hService($storeId); // phpcs:ignore
        if ((bool)$isEnabledGuaranteed24h && $this->service->isGuaranteed24hAllowed($request)) {
            $serviceList[] = ['Code' => '24H'];
        }

        // Express Delivery Service
        $expressDeliverCode = $requestData['express_delivery'] ?? $this->config->getExpressDeliveryServiceCode($storeId); // phpcs:ignore
        $isExpressDeliveryAllowed = $this->service->isExpressDeliveryAllowed($request, (string)$expressDeliverCode);
        if ($expressDeliverCode && $isExpressDeliveryAllowed) {
            $serviceList[] = [
                'Code' => $expressDeliverCode
            ];
        }

        // Contact Service
        $isEnabledContact = $requestData['cs1'] ?? $this->config->isEnabledContactService($storeId);
        if ((bool)$isEnabledContact && $this->service->isContactAllowed($request)) {
            $serviceList[] = [
                'Code' => 'CS1',
                'CS1Parameter' => ['Value' => $recipientPhoneNumber]
            ];
        }

        // Flexible Delivery Service
        $isEnabledFlexibleDelivery = $requestData['fds'] ?? $this->config->isEnabledFlexibleDeliveryService($storeId);
        $isFlexibleDeliveryAllowed = $this->service->isFlexibleDeliveryAllowed(
            $request,
            $isExpressDeliveryAllowed
        );
        if ((bool)$isEnabledFlexibleDelivery && $isFlexibleDeliveryAllowed) {
            $serviceList[] = [
                'Code' => 'FDS',
                'FDSParameter' => ['Value' => (string)$request->getRecipientEmail()]
            ];
        }

        // Flexible Delivery SMS Service
        $isEnabledFlexibleDeliverySms = $requestData['fss'] ?? $this->config->isEnabledFlexibleDeliverySmsService($storeId); // phpcs:ignore
        $isFlexibleDeliverySmsAllowed = $this->service->isFlexibleDeliverySmsAllowed(
            $request,
            $isEnabledFlexibleDelivery && $isFlexibleDeliveryAllowed
        );
        if ((bool)$isEnabledFlexibleDeliverySms && $isFlexibleDeliverySmsAllowed) {
            $serviceList[] = [
                'Code' => 'FSS',
                'FSSParameter' => ['Value' => $recipientPhoneNumber]
            ];
        }

        // SMS Service
        $isEnabledSms = $requestData['sm1'] ?? $this->config->isEnabledSmsService($storeId);
        $sm1Text = $this->config->getSmsServiceText($storeId);
        if ((bool)$isEnabledSms && $this->service->isSmsAllowed($request, $sm1Text)) {
            $serviceList[] = [
                'Code' => 'SM1',
                'SM1Parameter' => ['Value' => "{$recipientPhoneNumber}|$sm1Text"]
            ];
        }

        // SMS Pre-advice Service
        $isEnabledSmsPreAdvice = $requestData['sm2'] ?? $this->config->isEnabledSmsPreAdviceService($storeId);
        if ((bool)$isEnabledSmsPreAdvice && $this->service->isSmsPreAdviceAllowed($request)) {
            $serviceList[] = [
                'Code' => 'SM2',
                'SM2Parameter' => ['Value' => $recipientPhoneNumber]
            ];
        }

        // Addressee Only Service
        $isEnabledAddresseeOnly = $requestData['aos'] ?? $this->config->isEnabledAddresseeOnlyService($storeId);
        if ((bool)$isEnabledAddresseeOnly && $this->service->isAddresseeOnlyAllowed($request)) {
            $serviceList[] = [
                'Code' => 'AOS',
                'AOSParameter' => ['Value' => (string)$request->getRecipientContactPersonName()]
            ];
        }

        // Insurance Service
        $isEnabledInsurance = $requestData['ins'] ?? $this->config->isEnabledInsuranceService($storeId);
        if ((bool)$isEnabledInsurance && $this->service->isInsuranceAllowed($request)) {
            $serviceList[] = [
                'Code' => 'INS',
                'INSParameter' => ['Value' => $this->service->calculateInsuranceValue($request)]
            ];
        }

        if ($serviceList) {
            $parcel['ServiceList'] = $serviceList;
        }

        return [
            'ParcelList' => [$parcel],
            'TypeOfPrinter' => $this->config->getPrinterType($storeId),
            'PrintPosition' => $requestData['print_position'] ?? $this->config->getPrintPosition($storeId),
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
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return string
     */
    protected function generateReference(\Magento\Sales\Model\Order\Shipment $shipment): string
    {
        return str_replace(
            '{increment_id}',
            (string)$shipment->getOrder()->getIncrementId(),
            $this->config->getClientReference($shipment->getStoreId())
        );
    }
}
