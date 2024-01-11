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

class Mode implements OptionSourceInterface
{
    public const SANDBOX    = 'sandbox';
    public const PRODUCTION = 'production';

    /**
     * Option source for API modes.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SANDBOX, 'label' => 'Sandbox'],
            ['value' => self::PRODUCTION, 'label' => 'Production']
        ];
    }
}
