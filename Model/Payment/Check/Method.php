<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Payment\Check;

class Method
{
    /**
     * Check if it's COD payment method.
     *
     * @param string $paymentMethod
     * @return bool
     */
    public function isCashOnDelivery(string $paymentMethod): bool
    {
        return $paymentMethod === \Magento\OfflinePayments\Model\Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;
    }
}
