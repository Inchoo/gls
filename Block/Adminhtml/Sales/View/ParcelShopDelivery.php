<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Block\Adminhtml\Sales\View;

/**
 * @method \Magento\Sales\Model\Order|null getOrder()
 * @method bool|null getIsParcelShopDelivery()
 * @method string|null getCountryCode()
 * @method string|null getMapScriptUrl()
 * @method string|null getMapLanguageCode()
 */
class ParcelShopDelivery extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'GLSCroatia_Shipping::sales/view/parcel-shop-delivery.phtml';

    /**
     * @var \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery
     */
    protected \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery;

    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Json\Helper\Data|null $jsonHelper
     * @param \Magento\Directory\Helper\Data|null $directoryHelper
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\ViewModel\ParcelShopDelivery $parcelShopDelivery,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null,
        ?\Magento\Directory\Helper\Data $directoryHelper = null
    ) {
        $this->config = $config;
        $this->parcelShopDelivery = $parcelShopDelivery;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Prepare block data.
     *
     * @return self
     */
    protected function _prepareLayout()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $orderInfoBlock */
        $orderInfoBlock = $this->getLayout()->getBlock('order_info');

        if (!$orderInfoBlock || !$order = $orderInfoBlock->getParentBlock()->getOrder()) {
            return parent::_prepareLayout();
        }

        $this->setData('order', $order);
        $this->setData(
            'is_parcel_shop_delivery',
            $this->parcelShopDelivery->isParcelShopDeliveryMethod($order->getShippingMethod())
        );

        $deliveryData = $this->parcelShopDelivery->getParcelShopDeliveryPointData($order);
        $countryCode = $deliveryData->getData('contact/countryCode') ?: $order->getShippingAddress()->getCountryId();

        $this->setData('country_code', $countryCode);
        $this->setData('map_script_url', $this->config->getMapScriptUrlByCountryCode($countryCode));
        $this->setData('map_language_code', $this->config->getMapLanguageCode($countryCode));

        return parent::_prepareLayout();
    }
}
