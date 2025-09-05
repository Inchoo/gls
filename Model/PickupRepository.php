<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class PickupRepository
{
    /**
     * @var \GLSCroatia\Shipping\Model\Pickup[]
     */
    private array $cacheById = [];

    /**
     * @var \GLSCroatia\Shipping\Model\PickupFactory
     */
    protected \GLSCroatia\Shipping\Model\PickupFactory $pickupFactory;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Pickup
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Pickup $pickupResource;

    /**
     * @param \GLSCroatia\Shipping\Model\PickupFactory $pickupFactory
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Pickup $pickupResource
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\PickupFactory $pickupFactory,
        \GLSCroatia\Shipping\Model\ResourceModel\Pickup $pickupResource
    ) {
        $this->pickupFactory = $pickupFactory;
        $this->pickupResource = $pickupResource;
    }

    /**
     * Load pickup request by ID.
     *
     * @param int $entityId
     * @param bool $forceLoad
     * @return Pickup
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $entityId, bool $forceLoad = false): \GLSCroatia\Shipping\Model\Pickup
    {
        if (!$forceLoad && isset($this->cacheById[$entityId])) {
            return $this->cacheById[$entityId];
        }

        unset($this->cacheById[$entityId]);
        $pickup = $this->pickupFactory->create();
        $this->pickupResource->load($pickup, $entityId);

        if (!$pickup->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The pickup request with the Id: "%1" does not exist.', $entityId)
            );
        }

        return $this->cacheById[$entityId] = $pickup;
    }

    /**
     * Save pickup request.
     *
     * @param \GLSCroatia\Shipping\Model\Pickup $pickup
     * @return \GLSCroatia\Shipping\Model\Pickup
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \GLSCroatia\Shipping\Model\Pickup $pickup
    ): \GLSCroatia\Shipping\Model\Pickup {
        try {
            $this->pickupResource->save($pickup);
            return $this->get((int)$pickup->getId(), true);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the pickup request: %1', $e->getMessage())
            );
        }
    }

    /**
     * Delete pickup request.
     *
     * @param \GLSCroatia\Shipping\Model\Pickup $pickup
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\GLSCroatia\Shipping\Model\Pickup $pickup): bool
    {
        $id = (int)$pickup->getId();

        try {
            $this->pickupResource->delete($pickup);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete the pickup request: %1.', $e->getMessage())
            );
        }

        unset($this->cacheById[$id]);
        return true;
    }

    /**
     * Delete pickup request by ID.
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
