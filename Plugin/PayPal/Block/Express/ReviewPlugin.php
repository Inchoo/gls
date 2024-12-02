<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\PayPal\Block\Express;

class ReviewPlugin
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Remove GLS parcel shop delivery shipping option on express checkouts.
     *
     * @param \Magento\Paypal\Block\Express\Review $subject
     * @param string|array $key
     * @param mixed $value
     * @return array
     */
    public function beforeSetData(\Magento\Paypal\Block\Express\Review $subject, $key, $value = null): array
    {
        if ($key !== 'shipping_rate_groups'
            || !is_array($value)
            || !isset($value[\GLSCroatia\Shipping\Model\Carrier::CODE])
        ) {
            return [$key, $value];
        }

        /** @var \Magento\Quote\Model\Quote\Address\Rate[] $glsShippingMethods */
        $glsShippingMethods = $value[\GLSCroatia\Shipping\Model\Carrier::CODE] ?? [];
        foreach ($glsShippingMethods as $arrKey => $method) {
            if ($this->dataHelper->isLockerShopDeliveryMethod((string)$method->getMethod())) {
                unset($glsShippingMethods[$arrKey]);
                break;
            }
        }

        if ($glsShippingMethods) {
            $value[\GLSCroatia\Shipping\Model\Carrier::CODE] = array_values($glsShippingMethods);
        } else {
            unset($value[\GLSCroatia\Shipping\Model\Carrier::CODE]);
        }

        return [$key, $value];
    }

    /**
     * Unset GLS parcel shop delivery as current shipping rate on express checkouts.
     *
     * @param \Magento\Paypal\Block\Express\Review $subject
     * @param mixed $result
     * @return mixed|null
     */
    public function afterGetCurrentShippingRate(\Magento\Paypal\Block\Express\Review $subject, $result)
    {
        if ($result instanceof \Magento\Quote\Model\Quote\Address\Rate
            && $result->getCarrier() === \GLSCroatia\Shipping\Model\Carrier::CODE
            && $this->dataHelper->isLockerShopDeliveryMethod((string)$result->getMethod())
        ) {
            return null;
        }

        return $result;
    }
}
