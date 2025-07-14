<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Address;

class Edit extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'GLSCroatia_Shipping::shipping_address';

    /**
     * @var \GLSCroatia\Shipping\Model\AddressRepository
     */
    protected \GLSCroatia\Shipping\Model\AddressRepository $addressRepository;

    /**
     * @param \GLSCroatia\Shipping\Model\AddressRepository $addressRepository
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AddressRepository $addressRepository,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->addressRepository = $addressRepository;
        parent::__construct($context);
    }

    /**
     * The address edit action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->addressRepository->get((int)$id);
                $resultPage->getConfig()->getTitle()->set(__('Edit GLS Address'));
                return $resultPage;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e);
                $resultRedirect = $this->resultFactory->create(
                    \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
                );
                return $resultRedirect->setRefererOrBaseUrl();
            }
        }

        $resultPage->getConfig()->getTitle()->prepend(__('New GLS Address'));

        return $resultPage;
    }
}
