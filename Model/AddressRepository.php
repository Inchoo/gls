<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class AddressRepository
{
    /**
     * @var \GLSCroatia\Shipping\Model\Address[]
     */
    private array $cacheById = [];

    /**
     * @var \GLSCroatia\Shipping\Model\AddressFactory
     */
    protected \GLSCroatia\Shipping\Model\AddressFactory $addressFactory;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Address
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Address $addressResource;

    /**
     * @param \GLSCroatia\Shipping\Model\AddressFactory $addressFactory
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Address $addressResource
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AddressFactory $addressFactory,
        \GLSCroatia\Shipping\Model\ResourceModel\Address $addressResource
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressResource = $addressResource;
    }

    /**
     * Load address by ID.
     *
     * @param int $entityId
     * @param bool $forceLoad
     * @return \GLSCroatia\Shipping\Model\Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $entityId, bool $forceLoad = false): \GLSCroatia\Shipping\Model\Address
    {
        if (!$forceLoad && isset($this->cacheById[$entityId])) {
            return $this->cacheById[$entityId];
        }

        unset($this->cacheById[$entityId]);
        $address = $this->addressFactory->create();
        $this->addressResource->load($address, $entityId);

        if (!$address->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The address with the Id: "%1" does not exist.', $entityId)
            );
        }

        return $this->cacheById[$entityId] = $address;
    }

    /**
     * Save address.
     *
     * @param \GLSCroatia\Shipping\Model\Address $address
     * @return \GLSCroatia\Shipping\Model\Address
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\GLSCroatia\Shipping\Model\Address $address): \GLSCroatia\Shipping\Model\Address
    {
        try {
            $this->addressResource->save($address);
            return $this->get((int)$address->getId(), true);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('An address with the same data already exists.') // todo treba li?
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the address: %1', $e->getMessage())
            );
        }
    }

    /**
     * Delete address.
     *
     * @param \GLSCroatia\Shipping\Model\Address $address
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\GLSCroatia\Shipping\Model\Address $address): bool
    {
        $id = (int)$address->getId();

        try {
            $this->addressResource->delete($address);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete the address: %1.', $e->getMessage())
            );
        }

        unset($this->cacheById[$id]);
        return true;
    }

    /**
     * Delete address by ID.
     *
     * @param int $entityId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->get($entityId));
    }
}
