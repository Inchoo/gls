<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Ui\DataProvider\Form;

class AddressDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected array $loadedData = [];

    /**
     * @var \GLSCroatia\Shipping\Model\AddressRepository
     */
    protected \GLSCroatia\Shipping\Model\AddressRepository $addressRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected \Magento\Framework\App\RequestInterface $request;

    /**
     * @param \GLSCroatia\Shipping\Model\AddressRepository $addressRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AddressRepository $addressRepository,
        \Magento\Framework\App\RequestInterface $request,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->addressRepository = $addressRepository;
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
                $address = $this->addressRepository->get((int)$id);
                $this->loadedData[$id] = $address->getData();
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
