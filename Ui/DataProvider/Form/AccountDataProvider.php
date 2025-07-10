<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Ui\DataProvider\Form;

class AccountDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected array $loadedData = [];

    /**
     * @var \GLSCroatia\Shipping\Model\AccountRepository
     */
    protected \GLSCroatia\Shipping\Model\AccountRepository $accountRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected \Magento\Framework\App\RequestInterface $request;

    /**
     * @param \GLSCroatia\Shipping\Model\AccountRepository $accountRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AccountRepository $accountRepository,
        \Magento\Framework\App\RequestInterface $request,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->accountRepository = $accountRepository;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        if ($this->loadedData) {
            return $this->loadedData;
        }

        if ($id = $this->request->getParam($this->getRequestFieldName())) {
            try {
                $account = $this->accountRepository->get((int)$id);
                $data = $account->getData();
                if (isset($data['password']) && $data['password'] !== '') {
                    $data['password'] = '******';
                }
                $this->loadedData[$id] = $data;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->loadedData = [];
            }
        }

        return $this->loadedData;
    }

    /**
     * Add field filter to collection.
     *
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter) // phpcs:ignore
    {
        // this is empty by design
    }
}
