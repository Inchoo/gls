<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\PayPal\Block\Express;

use Magento\Paypal\Block\Express\Review;

class ReviewPlugin
{
    /**
     * Remove GLS parcel shop delivery shipping option on express checkouts.
     *
     * @param Review $subject
     * @param string|array $key
     * @param mixed $value
     * @return array
     */
    public function beforeSetData(Review $subject, $key, $value = null): array
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
            if ($method->getMethod() === \GLSCroatia\Shipping\Model\Carrier::PARCEL_SHOP_DELIVERY_METHOD) {
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
     * @param Review $subject
     * @param mixed $result
     * @return mixed|null
     */
    public function afterGetCurrentShippingRate(Review $subject, $result)
    {
        if ($result instanceof \Magento\Quote\Model\Quote\Address\Rate
            && $result->getCarrier() === \GLSCroatia\Shipping\Model\Carrier::CODE
            && $result->getMethod() === \GLSCroatia\Shipping\Model\Carrier::PARCEL_SHOP_DELIVERY_METHOD
        ) {
            return null;
        }

        return $result;
    }
}
