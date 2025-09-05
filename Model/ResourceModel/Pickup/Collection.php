<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel\Pickup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize collection.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \GLSCroatia\Shipping\Model\Pickup::class,
            \GLSCroatia\Shipping\Model\ResourceModel\Pickup::class
        );
        $this->_setIdFieldName('entity_id');
    }
}
