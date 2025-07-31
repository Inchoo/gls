<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Sales\Model\ResourceModel\Order\Shipment\Grid;

class CollectionPlugin
{
    /**
     * Add Parcel ID data to the shipment grid.
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject
     * @return void
     */
    public function beforeLoad(\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject): void
    {
        if ($subject->isLoaded()) {
            return;
        }

        $subject->getSelect()->joinLeft(
            ['gls_parcel' => $subject->getTable('gls_shipping_parcel')],
            'main_table.entity_id = gls_parcel.shipment_id',
            ['gls_parcel_id' => 'gls_parcel.parcel_id']
        );
    }

    /**
     * Prepare field before filtering.
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject
     * @param string|array $field
     * @param null|string|array $condition
     * @return array
     */
    public function beforeAddFieldToFilter(
        \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $subject,
        $field,
        $condition
    ): array {
        if ($field === 'order_id') {
            $field = 'main_table.order_id';
        }

        if ($field === 'gls_parcel_id') {
            $field = 'gls_parcel.parcel_id';
        }

        return [$field, $condition];
    }
}
