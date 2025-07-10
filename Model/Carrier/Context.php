<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Model\Api\Service
     */
    protected \GLSCroatia\Shipping\Model\Api\Service $apiService;

    /**
     * @var \GLSCroatia\Shipping\Model\Carrier\ShipmentRequestBuilder
     */
    protected \GLSCroatia\Shipping\Model\Carrier\ShipmentRequestBuilder $shipmentRequestBuilder;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    /**
     *
     * @var \Magento\Framework\App\State
     */
    protected \Magento\Framework\App\State $appState;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\Api\Service $apiService
     * @param \GLSCroatia\Shipping\Model\Carrier\ShipmentRequestBuilder $shipmentRequestBuilder
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\Api\Service $apiService,
        \GLSCroatia\Shipping\Model\Carrier\ShipmentRequestBuilder $shipmentRequestBuilder,
        \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\State $appState
    ) {
        $this->dataHelper = $dataHelper;
        $this->config = $config;
        $this->apiService = $apiService;
        $this->shipmentRequestBuilder = $shipmentRequestBuilder;
        $this->tablerateResource = $tablerateResource;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->appState = $appState;
    }

    /**
     * Get data helper.
     *
     * @return \GLSCroatia\Shipping\Helper\Data
     */
    public function getDataHelper(): \GLSCroatia\Shipping\Helper\Data
    {
        return $this->dataHelper;
    }

    /**
     * Get config.
     *
     * @return \GLSCroatia\Shipping\Model\Config
     */
    public function getConfig(): \GLSCroatia\Shipping\Model\Config
    {
        return $this->config;
    }

    /**
     * Get API service.
     *
     * @return \GLSCroatia\Shipping\Model\Api\Service
     */
    public function getApiService(): \GLSCroatia\Shipping\Model\Api\Service
    {
        return $this->apiService;
    }

    /**
     * Get shipment request builder.
     *
     * @return \GLSCroatia\Shipping\Model\Carrier\ShipmentRequestBuilder
     */
    public function getShipmentRequestBuilder(): \GLSCroatia\Shipping\Model\Carrier\ShipmentRequestBuilder
    {
        return $this->shipmentRequestBuilder;
    }

    /**
     * Get tablerate resource.
     *
     * @return \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate
     */
    public function getTablerateResource(): \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate
    {
        return $this->tablerateResource;
    }

    /**
     * Get data object factory.
     *
     * @return \Magento\Framework\DataObjectFactory
     */
    public function getDataObjectFactory(): \Magento\Framework\DataObjectFactory
    {
        return $this->dataObjectFactory;
    }

    /**
     * Get app state.
     *
     * @return \Magento\Framework\App\State
     */
    public function getAppState(): \Magento\Framework\App\State
    {
        return $this->appState;
    }
}
