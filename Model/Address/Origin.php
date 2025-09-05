<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Address;

class Origin
{
    /**
     * @var \GLSCroatia\Shipping\Model\AddressFactory
     */
    protected \GLSCroatia\Shipping\Model\AddressFactory $addressFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @param \GLSCroatia\Shipping\Model\AddressFactory $addressFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->addressFactory = $addressFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the address using the shipping origin data.
     *
     * @param int $websiteId
     * @return \GLSCroatia\Shipping\Model\Address
     */
    public function get(int $websiteId = 0): \GLSCroatia\Shipping\Model\Address
    {
        $storeInfo = (array)$this->scopeConfig->getValue(
            'general/store_information',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $shippingOrigin = (array)$this->scopeConfig->getValue(
            'shipping/origin',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $address = $this->addressFactory->create();
        $address->setCompany($storeInfo['name'] ?? null);
        $address->setPhoneNumber($storeInfo['phone'] ?? null);
        $address->setCountryCode($shippingOrigin['country_id'] ?? null);
        $address->setRegionId($shippingOrigin['region_id'] ?? null);
        $address->setPostcode($shippingOrigin['postcode'] ?? null);
        $address->setCity($shippingOrigin['city'] ?? null);
        $address->setStreet($shippingOrigin['street_line1'] ?? null);
        $address->setStreetLine2($shippingOrigin['street_line2'] ?? null);

        return $address;
    }

    /**
     * Set the shipping origin fallback data to the address.
     *
     * @param \GLSCroatia\Shipping\Model\Address $address
     * @param int $websiteId
     * @return \GLSCroatia\Shipping\Model\Address
     */
    public function set(
        \GLSCroatia\Shipping\Model\Address $address,
        int $websiteId = 0,
    ): \GLSCroatia\Shipping\Model\Address {
        $storeInfo = (array)$this->scopeConfig->getValue(
            'general/store_information',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        $shippingOrigin = (array)$this->scopeConfig->getValue(
            'shipping/origin',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        if (!$address->getCompany()) {
            $address->setCompany($storeInfo['name'] ?? null);
        }
        if (!$address->getPhoneNumber()) {
            $address->setPhoneNumber($storeInfo['phone'] ?? null);
        }
        if (!$address->getCountryCode()) {
            $address->setCountryCode($shippingOrigin['country_id'] ?? null);
        }
        if (!$address->getRegionId()) {
            $address->setRegionId($shippingOrigin['region_id'] ?? null);
        }
        if (!$address->getPostcode()) {
            $address->setPostcode($shippingOrigin['postcode'] ?? null);
        }
        if (!$address->getCity()) {
            $address->setCity($shippingOrigin['city'] ?? null);
        }
        if (!$address->getStreet()) {
            $address->setStreet($shippingOrigin['street_line1'] ?? null);
            $address->setStreetLine2($shippingOrigin['street_line2'] ?? null);
        }

        return $address;
    }
}
