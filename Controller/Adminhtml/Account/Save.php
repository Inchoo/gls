<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Account;

class Save extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'GLSCroatia_Shipping::shipping_account';

    /**
     * @var \GLSCroatia\Shipping\Model\AccountRepository
     */
    protected \GLSCroatia\Shipping\Model\AccountRepository $accountRepository;

    /**
     * @var \GLSCroatia\Shipping\Model\AccountFactory
     */
    protected \GLSCroatia\Shipping\Model\AccountFactory $accountFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\AccountRepository $accountRepository
     * @param \GLSCroatia\Shipping\Model\AccountFactory $accountFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AccountRepository $accountRepository,
        \GLSCroatia\Shipping\Model\AccountFactory $accountFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        parent::__construct($context);
    }

    /**
     * The account save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        if (!$data = $this->prepareData()) {
            $this->messageManager->addErrorMessage('Please ensure all required fields are filled.');
            return $resultRedirect->setRefererOrBaseUrl();
        }

        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                $account = $this->accountRepository->get((int)$id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addExceptionMessage($e);
                return $resultRedirect->setRefererOrBaseUrl();
            }
        } else {
            $account = $this->accountFactory->create();
        }

        $account->addData($data);

        try {
            $account = $this->accountRepository->save($account);
            $this->messageManager->addSuccessMessage('The GLS account has been successfully saved.');
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect->setPath('gls/account/edit', ['id' => $account->getId()]);
    }

    /**
     * Validate and prepare the save request.
     *
     * @return array
     */
    protected function prepareData(): array
    {
        $result = [];

        foreach (['client_id', 'username', 'password', 'country_code'] as $param) {
            $value = trim((string)$this->getRequest()->getParam($param, ''));
            if ($value === '') {
                return [];
            }

            if ($param === 'password' && $value === '******') {
                continue;
            }

            $result[$param] = $value;
        }

        return $result;
    }
}
