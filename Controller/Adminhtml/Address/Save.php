<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Address;

class Save extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'GLSCroatia_Shipping::shipping_address';

    /**
     * @var \GLSCroatia\Shipping\Model\AddressRepository
     */
    protected \GLSCroatia\Shipping\Model\AddressRepository $addressRepository;

    /**
     * @var \GLSCroatia\Shipping\Model\AddressFactory
     */
    protected \GLSCroatia\Shipping\Model\AddressFactory $addressFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\AddressRepository $addressRepository
     * @param \GLSCroatia\Shipping\Model\AddressFactory $addressFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AddressRepository $addressRepository,
        \GLSCroatia\Shipping\Model\AddressFactory $addressFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        parent::__construct($context);
    }

    /**
     * The address save action.
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
                $address = $this->addressRepository->get((int)$id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addExceptionMessage($e);
                return $resultRedirect->setRefererOrBaseUrl();
            }
        } else {
            $address = $this->addressFactory->create();
        }

        $address->addData($data);

        try {
            $address = $this->addressRepository->save($address);
            $this->messageManager->addSuccessMessage('The GLS address has been successfully saved.');
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect->setPath('gls/address/edit', ['id' => $address->getId()]);
    }

    /**
     * Validate and prepare the save request.
     *
     * @return array
     */
    protected function prepareData(): array
    {
        $result = [];

        // required fields
        foreach (['label', 'country_code', 'postcode', 'city', 'street'] as $param) {
            $value = trim((string)$this->getRequest()->getParam($param, ''));
            if ($value === '') {
                return [];
            }

            $result[$param] = $value;
        }

        // optional fields
        foreach (['company', 'phone_number', 'region_id', 'region', 'street_line2'] as $param) {
            $value = trim((string)$this->getRequest()->getParam($param, ''));
            $result[$param] = $value ?: null;
        }

        if ($result['region_id']) {
            $result['region'] = null;
        }

        return $result;
    }
}
