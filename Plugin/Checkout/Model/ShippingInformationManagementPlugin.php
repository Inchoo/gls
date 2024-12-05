<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Checkout\Model;

class ShippingInformationManagementPlugin
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
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->dataHelper = $dataHelper;
        $this->json = $json;
    }

    /**
     * Save GLS data to the shipping address.
     *
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Api\ShippingInformationManagementInterface $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ): void {
        $shippingAddress = $addressInformation->getShippingAddress();

        $glsData = $this->jsonDecode($shippingAddress->getData('gls_data') ?: '');

        $isParcelShopDelivery = $addressInformation->getShippingCarrierCode() === \GLSCroatia\Shipping\Model\Carrier::CODE // phpcs:ignore
            && $this->dataHelper->isLockerShopDeliveryMethod($addressInformation->getShippingMethodCode());

        $deliveryPoint = $this->jsonDecode(
            $addressInformation->getExtensionAttributes()->getGlsParcelShopDeliveryPoint() ?: ''
        );

        if ($isParcelShopDelivery && !$deliveryPoint) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid GLS Delivery Point.'));
        }
        if ($isParcelShopDelivery) {
            $glsData['parcelShopDeliveryPoint'] = $deliveryPoint;
        } else {
            unset($glsData['parcelShopDeliveryPoint']);
        }

        $glsData = $glsData ? $this->json->serialize($glsData) : null;
        $shippingAddress->setData('gls_data', $glsData);
    }

    /**
     * Decode JSON string.
     *
     * @param string $jsonString
     * @return array
     */
    protected function jsonDecode(string $jsonString): array
    {
        if (!$jsonString) {
            return [];
        }

        try {
            return $this->json->unserialize($jsonString);
        } catch (\InvalidArgumentException $e) {
            return [];
        }
    }
}
