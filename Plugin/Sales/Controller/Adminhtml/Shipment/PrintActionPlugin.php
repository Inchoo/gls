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
     * @param \Magento\Sales\Model\Order\Shipment[]|\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $result
     * @return array
     */
    public function beforeGetPdf(Shipment $subject, $result): array
    {
        foreach ($result as $shipment) {
            $order = $shipment->getOrder();

            if (!$this->parcelShopDelivery->isParcelShopDeliveryMethod($order->getShippingMethod())) {
                continue;
            }

            $deliveryPointData = $this->parcelShopDelivery->getParcelShopDeliveryPointData($order);
            if (!$deliveryPointData->getData()) {
                continue;
            }

            $shippingDescription = $order->getShippingDescription();
            $parcelShopId = (string)$deliveryPointData->getData('id');
            if ($parcelShopId && strpos($shippingDescription, $parcelShopId) === false) {
                $order->setShippingDescription("{$shippingDescription} {$parcelShopId}");
            }
        }

        return [$result];
    }
}
