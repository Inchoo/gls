<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class Account extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'gls_account';

    /**
     * Initialize model object.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\GLSCroatia\Shipping\Model\ResourceModel\Account::class);
    }

    /**
     * Get entity ID.
     *
     * @return int|string|null
     */
    public function getId()
    {
        return $this->getEntityId();
    }

    /**
     * Set entity ID.
     *
     * @param int|string|null $value
     * @return self
     */
    public function setId($value): self
    {
        return $this->setEntityId($value);
    }

    /**
     * Get entity ID.
     *
     * @return int|string|null
     */
    public function getEntityId()
    {
        return $this->_getData('entity_id');
    }

    /**
     * Set entity ID.
     *
     * @param int|string|null $entityId
     * @return self
     */
    public function setEntityId($entityId)
    {
        return $this->setData('entity_id', $entityId);
    }

    /**
     * Get client ID.
     *
     * @return int|string|null
     */
    public function getClientId(): int|string|null
    {
        return $this->_getData('client_id');
    }

    /**
     * Set client ID.
     *
     * @param int|string|null $clientId
     * @return self
     */
    public function setClientId(int|string|null $clientId): self
    {
        return $this->setData('client_id', $clientId);
    }

    /**
     * Get username.
     *
     * @return string|null
     */
    public function getUsername(): string|null
    {
        return $this->_getData('username');
    }

    /**
     * Set username.
     *
     * @param string|null $username
     * @return self
     */
    public function setUsername(string|null $username): self
    {
        return $this->setData('username', $username);
    }

    /**
     * Get password.
     *
     * @return string|null
     */
    public function getPassword(): string|null
    {
        return $this->_getData('password');
    }

    /**
     * Set password.
     *
     * @param string|null $password
     * @return self
     */
    public function setPassword(string|null $password): self
    {
        return $this->setData('password', $password);
    }

    /**
     * Get country code.
     *
     * @return string|null
     */
    public function getCountryCode(): string|null
    {
        return $this->_getData('country_code');
    }

    /**
     * Set country code.
     *
     * @param string|null $countryCode
     * @return self
     */
    public function setCountryCode(string|null $countryCode): self
    {
        return $this->setData('country_code', $countryCode);
    }
}
