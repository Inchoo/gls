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

class Saturation implements OptionSourceInterface
{
    public const HIGH_VOLUME  = 1;
    public const LOW_VOLUME   = 2;
    public const OUT_OF_ORDER = 3;

    /**
     * Option source for map saturation.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::HIGH_VOLUME, 'label' => 'High Volume'],
            ['value' => self::LOW_VOLUME, 'label' => 'Low Volume'],
            ['value' => self::OUT_OF_ORDER, 'label' => 'Out Of Order']
        ];
    }
}
