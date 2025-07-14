<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Ui\DataProvider\Form;

class PickupDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected array $loadedData = [];

    /**
     * @var \GLSCroatia\Shipping\Model\PickupRepository
     */
    protected \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected \Magento\Framework\App\RequestInterface $request;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    /**
     * @param \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\PickupRepository $pickupRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->pickupRepository = $pickupRepository;
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
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

        $data = $this->dataPersistor->get('pickup_request_data') ?: [];
        $this->dataPersistor->clear('pickup_request_data');

        if ($id = $this->request->getParam($this->getRequestFieldName())) {
            try {
                $pickup = $this->pickupRepository->get((int)$id);
                $this->loadedData[$id] = $pickup->getData();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->loadedData = [];
            }
        } else {
            $this->loadedData[null] = $data;
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
