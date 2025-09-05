<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

class Address implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Address
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Address $addressResource;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Address $addressResource
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Address $addressResource
    ) {
        $this->addressResource = $addressResource;
    }

    /**
     * Option source for GLS account data.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        try {
            $this->options = $this->addressResource->generateOptionsData();
        } catch (\Exception $e) {
            $this->options = [];
        }

        array_unshift($this->options, ['label' => __('-- Please Select --'), 'value' => '']);

        return $this->options;
    }
}
