<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Pickup;

class View extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'GLSCroatia_Shipping::shipping_pickup_request';

    /**
     * @var \GLSCroatia\Shipping\Model\PickupRepository
     */
    protected \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository;

    /**
     * @param \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->pickupRepository = $pickupRepository;
        parent::__construct($context);
    }

    /**
     * The pickup request view action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->pickupRepository->get((int)$id);
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
                $resultPage->getConfig()->getTitle()->set(__('GLS Pickup Request'));
                return $resultPage;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e);
            }
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
