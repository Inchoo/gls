<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\ViewModel;

use GLSCroatia\Shipping\Model\Carrier;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;

class ParcelShopDelivery implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(\Magento\Framework\Serialize\Serializer\Json $json)
    {
        $this->json = $json;
    }

    /**
     * Check if it is GLS parcel shop delivery method.
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isParcelShopDeliveryMethod(string $shippingMethod): bool
    {
        return $shippingMethod === Carrier::CODE . '_' . Carrier::PARCEL_SHOP_DELIVERY_METHOD;
    }

    /**
     * Extract GLS parcel shop delivery data from the order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getParcelShopDeliveryPointData(Order $order): array
    {
        if (!$glsDataJson = $order->getData('gls_data')) {
            return [];
        }

        try {
            $glsData = $this->json->unserialize($glsDataJson);
        } catch (\InvalidArgumentException $e) {
            return [];
        }

        return $glsData['parcelShopDeliveryPoint'] ?? [];
    }
}
