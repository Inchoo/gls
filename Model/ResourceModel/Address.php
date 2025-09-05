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
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList;

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        ?string $connectionName = null
    ) {
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context, $connectionName);
    }

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
     * Perform actions after address delete.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return self
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);

        // clean up the "core_config_data" table.
        $deletedRows = $this->getConnection()->delete(
            $this->getTable('core_config_data'),
            ['path = ?' => 'carriers/gls/address_id', 'value = ?' => $object->getId()]
        );

        if ($deletedRows) {
            $this->cacheTypeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        }

        return $this;
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
