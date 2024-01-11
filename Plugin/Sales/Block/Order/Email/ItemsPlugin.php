<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Sales\Block\Order\Email;

use Magento\Sales\Block\Items\AbstractItems;

class ItemsPlugin
{
    /**
     * Display GLS parcel shop delivery point on sales emails.
     *
     * @param \Magento\Sales\Block\Order\Email\Items|\Magento\Sales\Block\Order\Email\Shipment\Items $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(AbstractItems $subject, string $result): string
    {
        if ($childBlock = $subject->getChildBlock('gls_parcel_shop_delivery_point')) {
            $result = $childBlock->toHtml() . $result;
        }
        return $result;
    }
}
