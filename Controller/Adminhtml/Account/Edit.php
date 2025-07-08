<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Account;

class Edit extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'GLSCroatia_Shipping::shipping_account';

    /**
     * @var \GLSCroatia\Shipping\Model\AccountRepository
     */
    protected \GLSCroatia\Shipping\Model\AccountRepository $accountRepository;

    /**
     * @param \GLSCroatia\Shipping\Model\AccountRepository $accountRepository
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AccountRepository $accountRepository,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->accountRepository = $accountRepository;
        parent::__construct($context);
    }

    /**
     * The account edit action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->accountRepository->get((int)$id);
                $resultPage->getConfig()->getTitle()->set(__('Edit GLS Account'));
                return $resultPage;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e);
                return $this->resultRedirectFactory->create()->setRefererOrBaseUrl();
            }
        }

        $resultPage->getConfig()->getTitle()->prepend(__('New GLS Account'));

        return $resultPage;
    }
}
