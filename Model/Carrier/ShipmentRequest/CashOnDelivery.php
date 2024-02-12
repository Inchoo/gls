<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier\ShipmentRequest;

use Magento\Sales\Model\Order\Shipment;

class CashOnDelivery
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Model\Payment\Check\Method
     */
    protected \GLSCroatia\Shipping\Model\Payment\Check\Method $paymentMethodCheck;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected \Magento\Directory\Model\CurrencyFactory $currencyFactory;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\Payment\Check\Method $paymentMethodCheck
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\Payment\Check\Method $paymentMethodCheck,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->config = $config;
        $this->paymentMethodCheck = $paymentMethodCheck;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Check if Cash on Delivery is allowed for shipment.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return bool
     */
    public function isAllowed(Shipment $shipment): bool
    {
        $order = $shipment->getOrder();

        $payment = $order->getPayment();
        if (!$payment || !$this->paymentMethodCheck->isCashOnDelivery($payment->getMethod())) {
            return false; // not "cashondelivery" payment method
        }

        $firstShipment = $order->getShipmentsCollection()->getFirstItem();

        // COD is only available for the first shipment
        return (int)$shipment->getEntityId() === (int)$firstShipment->getEntityId();
    }

    /**
     * Calculate "CODAmount" value.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateAmount(Shipment $shipment): float
    {
        $targetCurrencyCode = $this->dataHelper->getConfigCode(
            'country_currency_code',
            $this->config->getApiCountryCode()
        );

        if (!$targetCurrencyCode) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not resolve GLS API currency code.')
            );
        }

        $order = $shipment->getOrder();
        $grandTotal = (float)$order->getBaseGrandTotal();
        $orderCurrencyCode = $order->getBaseCurrencyCode();

        if (strtolower($targetCurrencyCode) === strtolower($orderCurrencyCode)) {
            return $grandTotal;
        }

        $currencyModel = $this->currencyFactory->create()->load($orderCurrencyCode);

        // convert grand total amount from order base currency to target currency
        return (float)$currencyModel->convert($grandTotal, $targetCurrencyCode);
    }

    /**
     * Generate "CODReference" value.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return string
     */
    public function generateReference(Shipment $shipment): string
    {
        return "#{$shipment->getOrder()->getIncrementId()}";
    }
}
