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

class Country implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Option source for the currently supported countries.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = [];

        if ($countryIds = $this->config->getSupportedCountries()) {
            $collection = $this->collectionFactory->create();
            $collection->addCountryIdFilter($countryIds);
            $this->options = $collection->toOptionArray(false);
        }

        return $this->options;
    }
}
