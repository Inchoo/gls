<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\ViewModel;

class PackagingGlsOptions implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\AddressSwitcher
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\AddressSwitcher $addressSwitcher;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service $service;

    /**
     * @var \GLSCroatia\Shipping\Model\Config\Source\ExpressDeliveryCode
     */
    protected \GLSCroatia\Shipping\Model\Config\Source\ExpressDeliveryCode $expressDeliveryCodeOptions;

    /**
     * @var \GLSCroatia\Shipping\Model\Config\Source\PrintPosition
     */
    protected \GLSCroatia\Shipping\Model\Config\Source\PrintPosition $printPositionOptions;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected \Magento\Config\Model\Config\Source\Yesno $yesNoOptions;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected \Magento\Directory\Model\RegionFactory $regionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\AddressSwitcher $addressSwitcher
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service $service
     * @param \GLSCroatia\Shipping\Model\Config\Source\ExpressDeliveryCode $expressDeliveryCodeOptions
     * @param \GLSCroatia\Shipping\Model\Config\Source\PrintPosition $printPositionOptions
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNoOptions
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\AddressSwitcher $addressSwitcher,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service $service,
        \GLSCroatia\Shipping\Model\Config\Source\ExpressDeliveryCode $expressDeliveryCodeOptions,
        \GLSCroatia\Shipping\Model\Config\Source\PrintPosition $printPositionOptions,
        \Magento\Config\Model\Config\Source\Yesno $yesNoOptions,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->addressSwitcher = $addressSwitcher;
        $this->service = $service;
        $this->expressDeliveryCodeOptions = $expressDeliveryCodeOptions;
        $this->printPositionOptions = $printPositionOptions;
        $this->yesNoOptions = $yesNoOptions;
        $this->regionFactory = $regionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if it is GLS shipping carrier.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return bool
     */
    public function isGlsShippingMethod(\Magento\Sales\Model\Order\Shipment $shipment): bool
    {
        if ($shippingMethod = $shipment->getOrder()->getShippingMethod(true)) {
            return $shippingMethod->getData('carrier_code') === \GLSCroatia\Shipping\Model\Carrier::CODE;
        }

        return false;
    }

    /**
     * Get field config value.
     *
     * @param string $field
     * @param int $storeId
     * @return int|string|null
     */
    public function getFieldConfigValue(string $field, int $storeId)
    {
        return $this->config->getConfigValue($field, $storeId);
    }

    /**
     * Get field config flag.
     *
     * @param string $field
     * @param int $storeId
     * @return bool
     */
    public function getFieldConfigFlag(string $field, int $storeId): bool
    {
        return $this->config->getConfigFlag($field, $storeId);
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue(string $path, int $storeId)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Generate temporary shipment request.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return \Magento\Framework\DataObject
     */
    public function generateTempShipmentRequest(
        \Magento\Sales\Model\Order\Shipment $shipment
    ): \Magento\Framework\DataObject {
        $order = $shipment->getOrder();
        $storeId = (int)$shipment->getStoreId();

        /** @var \Magento\Shipping\Model\Shipment\Request $request */
        $request = $this->dataObjectFactory->create();
        $request->setOrderShipment($shipment);

        $originStreet1 = $this->getConfigValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS1, $storeId);
        $originStreet2 = $this->getConfigValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS2, $storeId);
        $originRegionCode = $this->getConfigValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
            $storeId
        );
        if (is_numeric($originRegionCode)) {
            $originRegionCode = $this->regionFactory->create()->load($originRegionCode)->getCode();
        }

        $request->setShipperAddressStreet(trim("{$originStreet1} {$originStreet2}"));
        $request->setShipperAddressStreet1($originStreet1);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(
            $this->getConfigValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY, $storeId)
        );
        $request->setShipperAddressStateOrProvinceCode($originRegionCode);
        $request->setShipperAddressPostalCode(
            $this->getConfigValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP, $storeId)
        );
        $request->setShipperAddressCountryCode(
            $this->getConfigValue(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID, $storeId)
        );

        $address = $order->getShippingAddress();

        $request->setRecipientContactPersonName(trim("{$address->getFirstname()} {$address->getLastname()}"));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim("{$address->getStreetLine(1)} {$address->getStreetLine(2)}"));
        $request->setRecipientAddressStreet1($address->getStreetLine(1));
        $request->setRecipientAddressStreet2($address->getStreetLine(2));
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode() ?: $address->getRegion());
        $request->setRecipientAddressRegionCode($address->getRegionCode());
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());

        $request->setShippingMethod($order->getShippingMethod(true)->getData('method'));
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($shipment->getPackages());
        $request->setBaseCurrencyCode($order->getStore()->getBaseCurrencyCode());
        $request->setStoreId($storeId);

        $package = []; // all items in one package
        foreach ($shipment->getItems() as $item) {
            $currentWeight = $package['params']['weight'] ?? 0;
            $currentPrice = $package['params']['customs_value'] ?? 0;

            $package['params']['weight'] = $currentWeight + ($item->getWeight() * $item->getQty());
            $package['params']['customs_value'] = $currentPrice + ($item->getPrice() * $item->getQty());

            $package['items'][$item->getOrderItemId()] = [
                'qty' => $item->getQty(),
                'customs_value' => $item->getPrice(),
                'price' => $item->getPrice(),
                'name' => $item->getName(),
                'weight' => $item->getWeight(),
                'product_id' => $item->getProductId(),
                'order_item_id' => $item->getOrderItemId()
            ];
        }
        $shipment->setPackages([$package]);

        if ($addressId = $this->config->getAddressId($storeId)) {
            $this->addressSwitcher->switchShipperAddress((int)$addressId, $request);
        }

        return $request;
    }

    /**
     * Is the "Cash on Delivery Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isCodServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isCashOnDeliveryAllowed($request);
    }

    /**
     * Generate "CODReference" value.
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     */
    public function getCodReference(\Magento\Framework\DataObject $request): string
    {
        return $this->service->getCashOnDeliveryReference($request);
    }

    /**
     * Is the "Guaranteed 24h Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isGuaranteed24hServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isGuaranteed24hAllowed($request);
    }

    /**
     * Is the "Express Delivery Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isExpressDeliveryServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isExpressDeliveryAllowed($request);
    }

    /**
     * Is the "Contact Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isContactServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isContactAllowed($request);
    }

    /**
     * Is the "Flexible Delivery Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isFlexibleDeliveryServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isFlexibleDeliveryAllowed($request, false);
    }

    /**
     * Is the "Flexible Delivery SMS Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isFlexibleDeliverySmsServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isFlexibleDeliverySmsAllowed(
            $request,
            $this->isFlexibleDeliveryServiceAllowed($request)
        );
    }

    /**
     * Is the "SMS Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @param int $storeId
     * @return bool
     */
    public function isSmsServiceAllowed(\Magento\Framework\DataObject $request, int $storeId): bool
    {
        return $this->service->isSmsAllowed(
            $request,
            $this->config->getSmsServiceText($storeId)
        );
    }

    /**
     * Is the "SMS Pre-advice Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isSmsPreAdviceServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isSmsPreAdviceAllowed($request);
    }

    /**
     * Is the "Addressee Only Service" allowed.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isAddresseeOnlyServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isAddresseeOnlyAllowed($request);
    }

    /**
     * Check if insurance is allowed for package value.
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    public function isInsuranceServiceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->service->isInsuranceAllowed($request);
    }

    /**
     * Get express delivery options.
     *
     * @return array
     */
    public function getExpressDeliveryCodeOptions(): array
    {
        return $this->expressDeliveryCodeOptions->toOptionArray();
    }

    /**
     * Get print position options.
     *
     * @return array
     */
    public function getPrintPositionOptions(): array
    {
        return $this->printPositionOptions->toOptionArray();
    }

    /**
     * Get yes/no options.
     *
     * @return array
     */
    public function getYesNoOptions(): array
    {
        return $this->yesNoOptions->toOptionArray();
    }
}
