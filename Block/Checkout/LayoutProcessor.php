<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
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
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected \Magento\Framework\View\Asset\Repository $assetRepository;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        \Magento\Framework\View\Asset\Repository $assetRepository
    ) {
        $this->config = $config;
        $this->arrayManager = $arrayManager;
        $this->assetRepository = $assetRepository;
    }

    /**
     * GLS UI Components.
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->config->isActive()) {
            $jsLayout = $this->removeGlsUiComponents($jsLayout);
        }

        if ($this->config->isEnabledCheckoutLogo()) {
            $jsLayout = $this->switchShippingMethodItemTemplate($jsLayout);
        }

        return $jsLayout;
    }

    /**
     * Remove GLS UI components.
     *
     * @param array $jsLayout
     * @return array
     */
    protected function removeGlsUiComponents(array $jsLayout): array
    {
        $parcelShopDeliveryPath = $this->arrayManager->findPath('gls-parcel-shop-delivery', $jsLayout);
        if ($parcelShopDeliveryPath) {
            $jsLayout = $this->arrayManager->remove($parcelShopDeliveryPath, $jsLayout);
        }

        $parcelShopDeliveryPathAddressPath = $this->arrayManager->findPath(
            'gls-parcel-shop-delivery-address',
            $jsLayout
        );
        if ($parcelShopDeliveryPathAddressPath) {
            $jsLayout = $this->arrayManager->remove($parcelShopDeliveryPathAddressPath, $jsLayout);
        }

        return $jsLayout;
    }

    /**
     * Switch to custom GLS "shippingMethodItemTemplate" template.
     *
     * @param array $jsLayout
     * @return array
     */
    protected function switchShippingMethodItemTemplate(array $jsLayout): array
    {
        if ($shippingAddressPath = $this->arrayManager->findPath('shippingAddress', $jsLayout)) {
            $shippingMethodItemTemplate = 'GLSCroatia_Shipping/checkout/shipping-address/shipping-method-item';

            $jsLayout = $this->arrayManager->set(
                "{$shippingAddressPath}/config/shippingMethodItemTemplate",
                $jsLayout,
                $shippingMethodItemTemplate
            );

            $jsLayout = $this->arrayManager->set(
                "{$shippingAddressPath}/config/glsLogoUrl",
                $jsLayout,
                $this->assetRepository->getUrl('GLSCroatia_Shipping/images/logo-128x128.jpg')
            );
        }

        return $jsLayout;
    }
}
