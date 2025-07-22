<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order', 'entity_id');
    }

    /**
     * Fetch "sales_order.gls_data" column value.
     *
     * @param int $orderId
     * @return string|null
     */
    public function fetchGlsData(int $orderId): ?string
    {
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), ['gls_data']);
        $select->where("{$this->getIdFieldName()} = ?", $orderId);

        $value = $this->getConnection()->fetchOne($select);
        return $value ? (string)$value : null;
    }

    /**
     * Update "sales_order.gls_data" column value.
     *
     * @param int $orderId
     * @param string $value
     * @return int
     */
    public function updateGlsData(int $orderId, string $value): int
    {
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['gls_data' => $value],
            ["{$this->getIdFieldName()} = ?" => $orderId]
        );
    }
}
