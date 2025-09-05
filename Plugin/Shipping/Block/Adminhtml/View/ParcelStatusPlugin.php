<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Shipping\Block\Adminhtml\View;

class ParcelStatusPlugin
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     */
    public function __construct(\GLSCroatia\Shipping\Helper\Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Add a link to the GLS parcel status on the order and shipment view pages.
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\AbstractOrder $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(\Magento\Sales\Block\Adminhtml\Order\AbstractOrder $subject, string $result): string
    {
        if (!$childBlock = $subject->getChildBlock('gls_parcel_status')) {
            return $result;
        }

        try {
            $order = $subject->getOrder();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $result;
        }

        if (!$this->isAllowed($order, $subject->getShipment())) {
            return $result;
        }

        $needle = '<div class="shipping-description-wrapper">'; // Magento_Shipping::view/form.phtml
        $position = strpos($result, $needle);
        if ($position !== false) {
            $insertPosition = $position + strlen($needle);
            return substr_replace($result, $childBlock->toHtml(), $insertPosition, 0);
        }

        $needle = '<div class="admin__page-section-item-content">'; // Magento_Shipping::order/view/info.phtml
        $position = strpos($result, $needle);
        if ($position !== false) {
            $insertPosition = $position + strlen($needle);
            return substr_replace($result, $childBlock->toHtml(), $insertPosition, 0);
        }

        return $result;
    }

    /**
     * Check if GLS parcel status link is allowed.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Order\Shipment|null $shipment
     * @return bool
     */
    protected function isAllowed(
        \Magento\Sales\Model\Order $order,
        ?\Magento\Sales\Model\Order\Shipment $shipment = null
    ): bool {
        $shippingMethod = $order->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== \GLSCroatia\Shipping\Model\Carrier::CODE) {
            return false;
        }

        $shipments = $shipment ? [$shipment] : $order->getShipmentsCollection()->getItems();
        foreach ($shipments as $item) {
            foreach ($item->getTracks() as $track) {
                if ($track->getCarrierCode() === \GLSCroatia\Shipping\Model\Carrier::CODE) {
                    return true;
                }
            }
        }

        return false;
    }
}
