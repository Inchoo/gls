<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Account;

class Delete extends \Magento\Backend\App\Action implements
    \Magento\Framework\App\Action\HttpPostActionInterface,
    \Magento\Framework\App\Action\HttpGetActionInterface
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
     * The account delete action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('gls/account/grid');

        if (!$id = $this->getRequest()->getParam('id')) {
            $this->messageManager->addErrorMessage(__('Cannot find a GLS account to delete.'));
            return $resultRedirect;
        }

        try {
            $this->accountRepository->deleteById((int)$id);
            $this->messageManager->addSuccessMessage(__('The GLS account has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect;
    }
}
