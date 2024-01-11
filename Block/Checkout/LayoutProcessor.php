<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected \Magento\Framework\Stdlib\ArrayManager $arrayManager;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager
    ) {
        $this->config = $config;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Remove UI components if the GLS carrier is disabled.
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $isCarrierActive = $this->config->isActive();

        $parcelShopDeliveryPath = $this->arrayManager->findPath('gls-parcel-shop-delivery', $jsLayout);
        if (!$isCarrierActive && $parcelShopDeliveryPath) {
            $jsLayout = $this->arrayManager->remove($parcelShopDeliveryPath, $jsLayout);
        }

        $parcelShopDeliveryPathAddressPath = $this->arrayManager->findPath(
            'gls-parcel-shop-delivery-address',
            $jsLayout
        );
        if (!$isCarrierActive && $parcelShopDeliveryPathAddressPath) {
            $jsLayout = $this->arrayManager->remove($parcelShopDeliveryPathAddressPath, $jsLayout);
        }

        return $jsLayout;
    }
}
