<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;

/**
 * Base for virtualType.
 */
class CarrierGeneric implements GenericInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier
     */
    protected \GLSCroatia\Shipping\Model\Carrier $glsCarrier;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @param \GLSCroatia\Shipping\Model\Carrier $glsCarrier
     * @param string $type
     */
    public function __construct(\GLSCroatia\Shipping\Model\Carrier $glsCarrier, string $type)
    {
        $this->glsCarrier = $glsCarrier;
        $this->type = $type;
    }

    /**
     * Option source for carrier data.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = [];

        foreach ($this->glsCarrier->getCode($this->type) as $code => $title) {
            $this->options[] = ['value' => $code, 'label' => $title];
        }

        return $this->options;
    }
}
