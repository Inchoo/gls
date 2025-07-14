<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Pickup;

class Request extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'GLSCroatia_Shipping::shipping_pickup_request';

    /**
     * @var \GLSCroatia\Shipping\Model\AccountRepository
     */
    protected \GLSCroatia\Shipping\Model\AccountRepository $accountRepository;
    /**
     * @var \GLSCroatia\Shipping\Model\AddressRepository
     */
    protected \GLSCroatia\Shipping\Model\AddressRepository $addressRepository;

    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \GLSCroatia\Shipping\Model\PickupRepository
     */
    protected \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository;

    /**
     * @var \GLSCroatia\Shipping\Model\PickupFactory
     */
    protected \GLSCroatia\Shipping\Model\PickupFactory $pickupFactory;

    /**
     * @var \GLSCroatia\Shipping\Model\Address\Origin
     */
    protected \GLSCroatia\Shipping\Model\Address\Origin $addressOrigin;

    /**
     * @var \GLSCroatia\Shipping\Model\Api\Service
     */
    protected \GLSCroatia\Shipping\Model\Api\Service $apiService;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $json;

    /**
     * @param \GLSCroatia\Shipping\Model\AccountRepository $accountRepository
     * @param \GLSCroatia\Shipping\Model\AddressRepository $addressRepository
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository
     * @param \GLSCroatia\Shipping\Model\PickupFactory $pickupFactory
     * @param \GLSCroatia\Shipping\Model\Address\Origin $addressOrigin
     * @param \GLSCroatia\Shipping\Model\Api\Service $apiService
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AccountRepository $accountRepository,
        \GLSCroatia\Shipping\Model\AddressRepository $addressRepository,
        \GLSCroatia\Shipping\Model\Config $config,
        \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository,
        \GLSCroatia\Shipping\Model\PickupFactory $pickupFactory,
        \GLSCroatia\Shipping\Model\Address\Origin $addressOrigin,
        \GLSCroatia\Shipping\Model\Api\Service $apiService,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->accountRepository = $accountRepository;
        $this->addressRepository = $addressRepository;
        $this->config = $config;
        $this->pickupRepository = $pickupRepository;
        $this->pickupFactory = $pickupFactory;
        $this->addressOrigin = $addressOrigin;
        $this->apiService = $apiService;
        $this->dataPersistor = $dataPersistor;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * The pickup request action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$data = $this->prepareData()) {
            $this->messageManager->addErrorMessage('Please ensure all required fields are filled.');
            return $this->getErrorResponse();
        }

        try {
            $account = $this->accountRepository->get((int)$data['account_id']);
            $data['client_number'] = $account->getClientId();

            $apiParams = $this->generateApiParams($data);
            $response = $this->apiService->createPickupRequest($apiParams);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
            return $this->getErrorResponse();
        }

        $body = $response->getDecodedBody();
        if ($pickupRequestErrors = $body['PickupRequestErrors'] ?? []) {
            $defaultErrorMessage = __('The GLS pickup request failed.');
            foreach ($pickupRequestErrors as $error) {
                $this->messageManager->addErrorMessage($error['ErrorDescription'] ?? $defaultErrorMessage);
            }
            return $this->getErrorResponse();
        }

        try {
            $pickup = $this->pickupFactory->create();
            $pickup->setAccount("{$account->getUsername()} ({$account->getClientId()})");
            $pickup->setCount($data['count']);
            $pickup->setTimeFrom($data['pickup_time_from']);
            $pickup->setTimeTo($data['pickup_time_to']);
            $pickup->setAddress($this->json->serialize($apiParams['Address']));

            $pickup = $this->pickupRepository->save($pickup);
            $this->messageManager->addSuccessMessage('The GLS pickup has been successfully requested.');
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->messageManager->addExceptionMessage($e);
            return $this->getErrorResponse();
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('gls/pickup/view', ['id' => $pickup->getId()]);
    }

    /**
     * Validate and prepare the pickup request data.
     *
     * @return array
     */
    protected function prepareData(): array
    {
        $requiredFields = ['account_id', 'website_id', 'count', 'pickup_time_from', 'pickup_time_to'];

        $result = [];
        foreach ($requiredFields as $param) {
            $value = trim((string)$this->getRequest()->getParam($param, ''));
            if ($value === '') {
                return [];
            }

            $result[$param] = $value;
        }

        $result['address_id'] = $this->getRequest()->getParam('address_id', 0);

        return $result;
    }

    /**
     * Generate API request params.
     *
     * @param array $data
     * @return array
     * @throws \DateMalformedStringException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function generateApiParams(array $data): array
    {
        $admin = $this->_auth->getUser();

        if ((int)$data['address_id'] === 0) {
            $address = $this->addressOrigin->get((int)$data['website_id']);
        } else {
            $address = $this->addressOrigin->set(
                $this->addressRepository->get((int)$data['address_id']),
                (int)$data['website_id']
            );
        }

        $street = $address->getStreet();
        if ($streetLine2 = $address->getStreetLine2()) {
            $street .= " {$streetLine2}";
        }

        $fromDateTime = new \DateTime($data['pickup_time_from']);
        $pickupTimeFrom = $fromDateTime->getTimestamp() * 1000;
        $toDateTime = new \DateTime($data['pickup_time_to']);
        $pickupTimeTo = $toDateTime->getTimestamp() * 1000;

        return [
            'Address' => [
                'ContactEmail' => $admin->getEmail(),
                'ContactName' => $admin->getName(),
                'ContactPhone' => $address->getPhoneNumber(),
                'Name' => $address->getCompany(),
                'CountryIsoCode' => $address->getCountryCode(),
                'ZipCode' => $address->getPostcode(),
                'City' => $address->getCity(),
                'Street' => $street
            ],
            'ClientNumber' => $data['client_number'],
            'Count' => $data['count'],
            'PickupTimeFrom' => "/Date({$pickupTimeFrom})/",
            'PickupTimeTo' => "/Date({$pickupTimeTo})/",
        ];
    }

    /**
     * Redirect back to the form using a data persistor.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getErrorResponse(): \Magento\Framework\Controller\ResultInterface
    {
        $this->dataPersistor->set('pickup_request_data', $this->getRequest()->getParams());

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('gls/pickup/create');
    }
}
