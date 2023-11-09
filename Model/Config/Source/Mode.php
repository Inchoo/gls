<?php

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
