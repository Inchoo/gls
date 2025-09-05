<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Ui\DataProvider\Listing;

class PickupDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Pickup\CollectionFactory
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Pickup\CollectionFactory $collectionFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Pickup\CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Pickup\CollectionFactory $collectionFactory,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Return collection.
     *
     * @return \GLSCroatia\Shipping\Model\ResourceModel\Pickup\Collection
     */
    public function getCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->collectionFactory->create();
        }

        return $this->collection;
    }
}
