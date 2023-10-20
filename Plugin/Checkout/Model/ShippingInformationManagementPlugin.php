<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Checkout\Model;

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
     * Save GLS delivery location to the shipping address.
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
        $shippingAddress->setData('gls_delivery_location', null);

        $methodCode = "{$addressInformation->getShippingCarrierCode()}_{$addressInformation->getShippingMethodCode()}";
        if ($methodCode !== 'gls_oohd') {
            return;
        }

        $deliverLocationJson = $addressInformation->getExtensionAttributes()->getGlsDeliveryLocation() ?: '';

        try {
            $deliveryLocation = $this->json->unserialize($deliverLocationJson);
        } catch (\InvalidArgumentException $e) {
            $deliveryLocation = [];
        }

        if (!$deliveryLocation) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid GLS Delivery Location.'));
        }

        $shippingAddress->setData('gls_delivery_location', $deliverLocationJson);
    }
}
