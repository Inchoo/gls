<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Sales\Controller\Adminhtml\Shipment;

use Magento\Sales\Model\Order\Pdf\Shipment;

class PrintActionPlugin
{
    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     */
    public function __construct(\GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery)
    {
        $this->parcelShopDelivery = $parcelShopDelivery;
    }

    /**
     * Add GLS parcel shop delivery data to the shipment PDF.
     *
     * @param \Magento\Sales\Model\Order\Pdf\Shipment $subject
     * @param \Magento\Sales\Model\Order\Shipment[] $result
     * @return array
     */
    public function beforeGetPdf(Shipment $subject, array $result): array
    {
        foreach ($result as $shipment) {
            $order = $shipment->getOrder();

            if (!$this->parcelShopDelivery->isParcelShopDeliveryMethod($order->getShippingMethod())
                || !$deliveryPointData = $this->parcelShopDelivery->getParcelShopDeliveryPointData($order)
            ) {
                continue;
            }

            $shippingDescription =  $order->getShippingDescription();
            $parcelShopId = $deliveryPointData['id'] ?? '';
            if (!str_contains($shippingDescription, $parcelShopId)) {
                $order->setShippingDescription("{$shippingDescription} {$parcelShopId}");
            }
        }

        return [$result];
    }
}
