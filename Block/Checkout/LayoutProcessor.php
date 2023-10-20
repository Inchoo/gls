<?php

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

        $outOfHomeDeliveryPath = $this->arrayManager->findPath('gls-out-of-home-delivery', $jsLayout);
        if (!$isCarrierActive && $outOfHomeDeliveryPath) {
            $jsLayout = $this->arrayManager->remove($outOfHomeDeliveryPath, $jsLayout);
        }

        $deliveryLocationAddressPath = $this->arrayManager->findPath('gls-delivery-location-address', $jsLayout);
        if (!$isCarrierActive && $deliveryLocationAddressPath) {
            $jsLayout = $this->arrayManager->remove($deliveryLocationAddressPath, $jsLayout);
        }

        return $jsLayout;
    }
}
