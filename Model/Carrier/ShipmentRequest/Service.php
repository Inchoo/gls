<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier\ShipmentRequest;

class Service
{
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
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\CashOnDelivery $cashOnDelivery
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\ExpressDelivery $expressDelivery
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Insurance $insurance
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\CashOnDelivery $cashOnDelivery,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\ExpressDelivery $expressDelivery,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Insurance $insurance
    ) {
        $this->dataHelper = $dataHelper;
        $this->cashOnDelivery = $cashOnDelivery;
        $this->expressDelivery = $expressDelivery;
        $this->insurance = $insurance;
    }

    /**
     * Is the "Cash on Delivery Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isCashOnDeliveryAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->cashOnDelivery->isAllowed($request->getOrderShipment());
    }

    /**
     * Calculate "CODAmount" value.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCashOnDeliveryAmount(\Magento\Framework\DataObject $request): float
    {
        return $this->cashOnDelivery->calculateAmount($request->getOrderShipment());
    }

    /**
     * Generate "CODReference" value.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return string
     */
    public function getCashOnDeliveryReference(\Magento\Framework\DataObject $request): string
    {
        return $this->cashOnDelivery->generateReference($request->getOrderShipment());
    }

    /**
     * Is the "Guaranteed 24h Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isGuaranteed24hAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $request->getRecipientAddressCountryCode() !== 'RS';
    }

    /**
     * Is the "Express Delivery Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param string|null $expressDeliverCode
     * @return bool
     */
    public function isExpressDeliveryAllowed(
        \Magento\Framework\DataObject $request,
        ?string $expressDeliverCode = null
    ): bool {
        if ($expressDeliverCode === null) {
            return $this->expressDelivery->isAllowedShipmentRequest($request);
        }

        return $this->expressDelivery->isAllowed($expressDeliverCode, $request);
    }

    /**
     * Is the "Contact Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isContactAllowed(\Magento\Framework\DataObject $request): bool
    {
        return !$this->dataHelper->isLockerShopDeliveryMethod($request->getShippingMethod());
    }

    /**
     * Is the "Flexible Delivery Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param bool $isExpressDeliveryServiceUsed
     * @return bool
     */
    public function isFlexibleDeliveryAllowed(
        \Magento\Framework\DataObject $request,
        bool $isExpressDeliveryServiceUsed
    ): bool {
        return !$isExpressDeliveryServiceUsed
            && !$this->dataHelper->isLockerShopDeliveryMethod($request->getShippingMethod());
    }

    /**
     * Is the "Flexible Delivery SMS Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param bool $isFlexibleDeliveryServiceUsed
     * @return bool
     */
    public function isFlexibleDeliverySmsAllowed(
        \Magento\Framework\DataObject $request,
        bool $isFlexibleDeliveryServiceUsed
    ): bool {
        return $isFlexibleDeliveryServiceUsed;
    }

    /**
     * Is the "SMS Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param string $smsTxt
     * @return bool
     */
    public function isSmsAllowed(\Magento\Framework\DataObject $request, string $smsTxt): bool
    {
        return (bool)$smsTxt;
    }

    /**
     * Is the "SMS Pre-advice Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isSmsPreAdviceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return true;
    }

    /**
     * Is the "Addressee Only Service" allowed.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isAddresseeOnlyAllowed(\Magento\Framework\DataObject $request): bool
    {
        return !$this->dataHelper->isLockerShopDeliveryMethod($request->getShippingMethod());
    }

    /**
     * Check if insurance is allowed for package value.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isInsuranceAllowed(\Magento\Framework\DataObject $request): bool
    {
        return $this->insurance->isAllowed(
            $request->getOrderShipment(),
            (string)$request->getShipperAddressCountryCode(),
            (string)$request->getRecipientAddressCountryCode()
        );
    }

    /**
     * Calculate shipment value.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return float
     */
    public function calculateInsuranceValue(\Magento\Framework\DataObject $request): float
    {
        return $this->insurance->calculateValue($request->getOrderShipment());
    }
}
