<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class AccountRepository
{
    /**
     * @var \GLSCroatia\Shipping\Model\Account[]
     */
    private array $cacheById = [];

    /**
     * @var \GLSCroatia\Shipping\Model\AccountFactory
     */
    protected \GLSCroatia\Shipping\Model\AccountFactory $accountFactory;

    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Account
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Account $accountResource;

    /**
     * @param \GLSCroatia\Shipping\Model\AccountFactory $accountFactory
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Account $accountResource
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AccountFactory $accountFactory,
        \GLSCroatia\Shipping\Model\ResourceModel\Account $accountResource
    ) {
        $this->accountFactory = $accountFactory;
        $this->accountResource = $accountResource;
    }

    /**
     * Load account by ID.
     *
     * @param int $entityId
     * @param bool $forceLoad
     * @return \GLSCroatia\Shipping\Model\Account
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $entityId, bool $forceLoad = false): \GLSCroatia\Shipping\Model\Account
    {
        if (!$forceLoad && isset($this->cacheById[$entityId])) {
            return $this->cacheById[$entityId];
        }

        unset($this->cacheById[$entityId]);
        $account = $this->accountFactory->create();
        $this->accountResource->load($account, $entityId);

        if (!$account->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The account with the Id: "%1" does not exist.', $entityId)
            );
        }

        return $this->cacheById[$entityId] = $account;
    }

    /**
     * Save account.
     *
     * @param \GLSCroatia\Shipping\Model\Account $account
     * @return \GLSCroatia\Shipping\Model\Account
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\GLSCroatia\Shipping\Model\Account $account): \GLSCroatia\Shipping\Model\Account
    {
        try {
            $this->accountResource->save($account);
            return $this->get((int)$account->getId(), true);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('An account with the same data already exists.')
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the account: %1', $e->getMessage())
            );
        }
    }

    /**
     * Delete account.
     *
     * @param \GLSCroatia\Shipping\Model\Account $account
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\GLSCroatia\Shipping\Model\Account $account): bool
    {
        $id = (int)$account->getId();

        try {
            $this->accountResource->delete($account);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __('Could not delete the account: %1.', $e->getMessage())
            );
        }

        unset($this->cacheById[$id]);
        return true;
    }

    /**
     * Delete account by ID.
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
