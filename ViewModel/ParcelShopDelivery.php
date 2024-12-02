<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\ViewModel;

class ParcelShopDelivery implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->json = $json;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Check if it is GLS parcel locker/shop delivery method.
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isParcelShopDeliveryMethod(string $shippingMethod): bool
    {
        return $this->dataHelper->isLockerShopDeliveryMethod($shippingMethod);
    }

    /**
     * Extract GLS parcel shop delivery data from the order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\DataObject
     */
    public function getParcelShopDeliveryPointData(\Magento\Sales\Model\Order $order): \Magento\Framework\DataObject
    {
        $dataObject = $this->dataObjectFactory->create();

        if (!$glsDataJson = $order->getData('gls_data')) {
            return $dataObject;
        }

        try {
            $glsData = $this->json->unserialize($glsDataJson);
        } catch (\InvalidArgumentException $e) {
            return $dataObject;
        }

        return $dataObject->setData($glsData['parcelShopDeliveryPoint'] ?? []);
    }
}
