<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

class Region implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Option source for regions.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $collection = $this->collectionFactory->create();
            $this->options = $collection->toOptionArray();
        }

        return $this->options;
    }
}
