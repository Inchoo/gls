<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Checkout;

use GLSCroatia\Shipping\Model\Carrier;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Model\Quote\Address;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected \Magento\Checkout\Model\Session $checkoutSession;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->json = $json;
    }

    /**
     * Add GLS data to the checkout configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        if (!$this->config->isActive()) {
            return [];
        }

        $quote = $this->checkoutSession->getQuote();
        $quoteGlsData = $this->extractGlsData($quote->getShippingAddress());

        $configData = [
            'mapScriptUrl' => $this->config->getMapScriptUrl(),
            'supportedCountries' => array_values($this->config->getSupportedCountries()),
            'parcelShopDelivery' => [
                'shippingMethodCode' => Carrier::CODE . '_' . Carrier::PARCEL_SHOP_DELIVERY_METHOD,
                'deliveryPoint' => $quoteGlsData['parcelShopDeliveryPoint'] ?? null
            ]
        ];

        return ['glsData' => $configData];
    }

    /**
     * Extract GLS data from the shipping address.
     *
     * @param Address $shippingAddress
     * @return array
     */
    protected function extractGlsData(Address $shippingAddress): array
    {
        if (!$deliveryLocation = $shippingAddress->getData('gls_data')) {
            return [];
        }

        try {
            return $this->json->unserialize($deliveryLocation);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }
}
