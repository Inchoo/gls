<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Block\Adminhtml\Sales\View\ParcelShopDelivery;

/**
 * @method \Magento\Sales\Model\Order|null getOrder()
 * @method \Magento\Framework\DataObject|null getDeliveryData()
 */
class Address extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'GLSCroatia_Shipping::sales/view/parcel-shop-delivery/address.phtml';

    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $registry;

    /**
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Json\Helper\Data|null $jsonHelper
     * @param \Magento\Directory\Helper\Data|null $directoryHelper
     */
    public function __construct(
        \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null,
        ?\Magento\Directory\Helper\Data $directoryHelper = null
    ) {
        $this->parcelShopDelivery = $parcelShopDelivery;
        $this->registry = $registry;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Prepare block data.
     *
     * @return self
     */
    protected function _prepareLayout()
    {
        if (!$order = $this->getCurrentOrder()) {
            return parent::_prepareLayout();
        }

        $this->setData('order', $order);
        $this->setData('delivery_data', $this->parcelShopDelivery->getParcelShopDeliveryPointData($order));

        return parent::_prepareLayout();
    }

    /**
     * Get current order.
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getCurrentOrder(): ?\Magento\Sales\Model\Order
    {
        $parentBlock = $this->getParentBlock();

        if (!$parentBlock || !$order = $parentBlock->getOrder()) {
            $order = $this->registry->registry('current_order');
        }

        return $order;
    }
}
