<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

class Country implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var string
     */
    protected string $configField;

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
     * @param string $configField
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory,
        string $configField = ''
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->configField = $configField;
    }

    /**
     * Option source for countries.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $collection = $this->collectionFactory->create();

        if ($this->configField && $countryIds = $this->config->getSupportedCountries($this->configField)) {
            $collection->addCountryIdFilter($countryIds);
        }

        return $this->options = $collection->toOptionArray(false);
    }
}
