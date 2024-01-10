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

class PrinterType implements OptionSourceInterface
{
    /**
     * Option source for printer types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'A4_2x2', 'label' => 'A4_2x2'],
            ['value' => 'A4_4x1', 'label' => 'A4_4x1'],
            ['value' => 'Connect', 'label' => 'Connect'],
            ['value' => 'Thermo', 'label' => 'Thermo']
        ];
    }
}
