<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Checkout\Model;

use GLSCroatia\Shipping\Model\Carrier;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

class ShippingInformationManagementPlugin
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
     * Save GLS data to the shipping address.
     *
     * @param ShippingInformationManagementInterface $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ): void {
        $shippingAddress = $addressInformation->getShippingAddress();

        $glsData = $this->jsonDecode($shippingAddress->getData('gls_data') ?: '');

        $isParcelShopDelivery = $addressInformation->getShippingCarrierCode() === Carrier::CODE
            && $addressInformation->getShippingMethodCode() === Carrier::PARCEL_SHOP_DELIVERY_METHOD;

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
