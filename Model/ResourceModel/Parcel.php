<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel;

class Parcel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gls_shipping_parcel', 'entity_id');
    }

    /**
     * Insert/update row in the table.
     *
     * @param array $insertData
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertRow(array $insertData): int
    {
        return $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            $insertData,
            array_keys($insertData)
        );
    }
}
