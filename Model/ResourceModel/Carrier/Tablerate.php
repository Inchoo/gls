<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel\Carrier;

class Tablerate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    protected array $csvHeaderColumns;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory $rateQueryFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory $rateQueryFactory
     * @param array $csvHeaderColumns
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory $rateQueryFactory,
        array $csvHeaderColumns,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        ?string $connectionName = null
    ) {
        $this->rateQueryFactory = $rateQueryFactory;
        $this->csvHeaderColumns = $csvHeaderColumns;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gls_shipping_tablerate', 'entity_id');
    }

    /**
     * Retrieve the header columns for the GLS table rates CSV.
     *
     * @return array
     */
    public function getCsvHeaderColumns(): array
    {
        return $this->csvHeaderColumns;
    }

    /**
     * Fetch the best available shipping table rate.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request): array
    {
        $rateQuery = $this->rateQueryFactory->create(['request' => $request]);

        $select = $this->getConnection()->select();
        $select->from($this->getMainTable());
        $rateQuery->prepareSelect($select);

        return $this->getConnection()->fetchRow($select, $rateQuery->getBindings()) ?: [];
    }

    /**
     * Export the current GLS table rates for the website.
     *
     * @param int $websiteId
     * @param int $currentPage
     * @param int $pageSize
     * @return array
     */
    public function exportRates(int $websiteId, int $currentPage, int $pageSize = 200): array
    {
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable(), $this->getCsvHeaderColumns());
        $select->where('website_id = ?', $websiteId);
        $select->order('entity_id');
        $select->limitPage($currentPage, $pageSize);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Import the new GLS table rates for the website.
     *
     * @param array $rates
     * @return int
     */
    public function importRates(array $rates): int
    {
        return $this->getConnection()->insertMultiple($this->getMainTable(), $rates);
    }

    /**
     * Delete the GLS table rates for the website.
     *
     * @param int $websiteId
     * @return int
     */
    public function deleteRates(int $websiteId): int
    {
        return $this->getConnection()->delete($this->getMainTable(), ['website_id = ?' => $websiteId]);
    }
}
