<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

class Account implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Account
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Account $accountResource;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Account $accountResource
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Account $accountResource
    ) {
        $this->accountResource = $accountResource;
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
            $this->options = $this->accountResource->generateOptionsData();
        } catch (\Exception $e) {
            $this->options = [];
        }

        array_unshift($this->options, ['label' => __('-- Please Select --'), 'value' => '']);

        return $this->options;
    }
}
