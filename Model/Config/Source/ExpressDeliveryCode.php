<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExpressDeliveryCode implements OptionSourceInterface
{
    /**
     * Option source for express delivery service codes.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => ' '],
            ['value' => 'T09', 'label' => '09:00'],
            ['value' => 'T10', 'label' => '10:00'],
            ['value' => 'T12', 'label' => '12:00']
        ];
    }
}
