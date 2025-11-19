<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate;

class RateQuery
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateRequest
     */
    protected \Magento\Quote\Model\Quote\Address\RateRequest $request;

    /**
     * @var \Magento\Framework\DB\Sql\ExpressionFactory
     */
    protected \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory;

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateRequest $request,
        \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory
    ) {
        $this->request = $request;
        $this->expressionFactory = $expressionFactory;
    }

    /**
     * Get shipping rate request.
     *
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function getRequest(): \Magento\Quote\Model\Quote\Address\RateRequest
    {
        return $this->request;
    }

    /**
     * Prepare a database query for GLS table rates.
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    public function prepareSelect(\Magento\Framework\DB\Select $select): \Magento\Framework\DB\Select
    {
        $request = $this->getRequest();

        $select->where('website_id = :website_id');
        $select->limit(1);

        $destinationOrWhere = [
            '(country_code = :country_code AND region_code = :region_code AND postcode = :postcode)',
            '(country_code = :country_code AND region_code = :region_code AND postcode = :postcode_prefix)',
            '(country_code = :country_code AND region_code = "*" AND postcode = :postcode)',
            '(country_code = :country_code AND region_code = "*" AND postcode = :postcode_prefix)',
            '(country_code = "*" AND region_code = "*" AND postcode = :postcode)',
            '(country_code = "*" AND region_code = "*" AND postcode = :postcode_prefix)',
            '(country_code = :country_code AND region_code = :region_code AND postcode = "*")',
            '(country_code = :country_code AND region_code = "*" AND postcode = "*")',
            '(country_code = "*" AND region_code = "*" AND postcode = "*")'
        ];
        $select->where(implode(' OR ', $destinationOrWhere));

        $conditions = $request->getData('gls_conditions') ?: []; // the order of values in the array is important
        if ($conditionsWhere = $this->prepareConditionsWhere($conditions)) {
            $select->where($conditionsWhere);
        }

        $orderBy = [
            $this->expressionFactory->create(['expression' => 'CASE WHEN postcode = "*" THEN 0 ELSE 1 END DESC']),
            $this->expressionFactory->create(['expression' => 'CASE WHEN region_code = "*" THEN 0 ELSE 1 END DESC']),
            $this->expressionFactory->create(['expression' => 'CASE WHEN country_code = "*" THEN 0 ELSE 1 END DESC'])
        ];

        if ($conditionsOrderBy = $this->prepareConditionsOrderBy($conditions)) {
            array_push($orderBy, ...$conditionsOrderBy);
        }

        $orderBy[] = 'price ASC';
        $select->order($orderBy);

        return $select;
    }

    /**
     * Retrieve bindings for a GLS table rates database query.
     *
     * @return array
     */
    public function getBindings(): array
    {
        $request = $this->getRequest();

        $bindings = [
            'website_id' => (int)$request->getWebsiteId(),
            'country_code' => $request->getDestCountryId() ?: '*',
            'region_code' => $request->getDestRegionCode() ?: '*',
            'postcode' => $request->getDestPostcode() ?: '*',
            'postcode_prefix' => explode('-', (string)$request->getDestPostcode())[0] ?: '*'
        ];

        foreach ($request->getData('gls_conditions') ?: [] as $conditionName) {
            if ($conditionName === 'weight') {
                $bindings[$conditionName] = $request->getData('gls_package_weight');
            } elseif ($conditionName === 'subtotal') {
                $bindings[$conditionName] = $request->getData('gls_package_subtotal');
            } elseif ($conditionName === 'quantity') {
                $bindings[$conditionName] = $request->getData('gls_package_qty');
            } else {
                $bindings[$conditionName] = $request->getData($conditionName);
            }
        }

        return $bindings;
    }

    /**
     * @param array $conditions
     * @return string
     */
    public function prepareConditionsWhere(array $conditions): string
    {
        $conditionsWhere = [];
        foreach ($conditions as $conditionName) {
            $conditionsWhere[] = "{$conditionName} <= :{$conditionName}";
        }

        return implode(' AND ', $conditionsWhere);
    }

    /**
     * @param array $conditions
     * @return array
     */
    public function prepareConditionsOrderBy(array $conditions): array
    {
        $conditionsOrderBy = [];
        foreach ($conditions as $conditionName) {
            $conditionsOrderBy[] = "{$conditionName} DESC";
        }

        return $conditionsOrderBy;
    }
}
