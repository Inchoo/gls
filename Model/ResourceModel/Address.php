<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel;

class Address extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gls_shipping_address', 'entity_id');
    }

    /**
     * Generate source options data.
     *
     * @return array
     */
    public function generateOptionsData(): array
    {
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), ['entity_id', 'label']);

        $stmt = $this->getConnection()->query($select);

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = [
                'value' => $row['entity_id'],
                'label' => $row['label']
            ];
        }

        return $result;
    }
}
