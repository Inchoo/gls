<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Observer;

class ShipmentSaveAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Parcel
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Parcel $parcelResource;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Parcel $parcelResource
     */
    public function __construct(\GLSCroatia\Shipping\Model\ResourceModel\Parcel $parcelResource)
    {
        $this->parcelResource = $parcelResource;
    }

    /**
     * Save Parcel ID data.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getData('shipment');

        if ($parcelId = $shipment->getData('gls_parcel_id')) {
            try {
                $this->parcelResource->insertRow([
                    'order_id' => (int)$shipment->getOrderId(),
                    'shipment_id' => (int)$shipment->getId(),
                    'parcel_id' => (int)$parcelId
                ]);
            } catch (\Exception $e) {
                return;
            }
        }
    }
}
