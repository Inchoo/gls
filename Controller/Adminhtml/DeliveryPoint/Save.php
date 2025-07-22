<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\DeliveryPoint;

class Save extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Order
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Order $orderResource;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $registry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Order $orderResource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Order $orderResource,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->orderResource = $orderResource;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * The delivery point save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$order = $this->getOrder()) {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('sales/order/index');
        }

        if (!$deliveryData = $this->prepareDeliverData()) {
            $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
            return $resultJson->setData(['error' => true, 'message' => __('Invalid delivery point data.')]);
        }

        try {
            $glsData = $this->getCurrentGlsData((int)$order->getId());
            $glsData['parcelShopDeliveryPoint'] = $deliveryData;

            $glsDataJson = $this->json->serialize($glsData);
            $this->orderResource->updateGlsData((int)$order->getId(), $glsDataJson);

            $order->setData('gls_data', $glsDataJson);
        } catch (\Exception $e) {
            $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
            return $resultJson->setData(['error' => true, 'message' => $e->getMessage()]);
        }

        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
    }

    /**
     * Load order.
     *
     * @return \Magento\Sales\Model\Order|null
     */
    protected function getOrder(): ?\Magento\Sales\Model\Order
    {
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            try {
                $order = $this->orderRepository->get((int)$orderId);
                $this->registry->register('sales_order', $order);
                $this->registry->register('current_order', $order);

                return $order;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Validate and prepare delivery point data.
     *
     * @return array
     */
    protected function prepareDeliverData(): array
    {
        if (!$deliveryDataJson = $this->getRequest()->getParam('delivery_data')) {
            return [];
        }

        return $this->json->unserialize($deliveryDataJson);
    }

    /**
     * Get order current GLS data.
     *
     * @param int $orderId
     * @return array
     */
    protected function getCurrentGlsData(int $orderId): array
    {
        if ($value = $this->orderResource->fetchGlsData((int)$orderId)) {
            return $this->json->unserialize($value);
        }

        return [];
    }
}
