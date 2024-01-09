<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Sales\Block\Order;

use Magento\Sales\Block\Order\Info;

class InfoPlugin
{
    /**
     * Display GLS parcel shop delivery point on customer account order views.
     *
     * @param \Magento\Sales\Block\Order\Info $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(Info $subject, string $result): string
    {
        if ($childBlock = $subject->getChildBlock('gls_parcel_shop_delivery_point')) {
            $result .= $childBlock->toHtml();
        }
        return $result;
    }
}
