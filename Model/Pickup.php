<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class Pickup extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'gls_pickup_request';

    /**
     * Initialize model object.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\GLSCroatia\Shipping\Model\ResourceModel\Pickup::class);
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
     * Get account.
     *
     * @return string|null
     */
    public function getAccount()
    {
        return $this->_getData('account');
    }

    /**
     * Set account.
     *
     * @param string|null $account
     * @return self
     */
    public function setAccount($account)
    {
        return $this->setData('account', $account);
    }

    /**
     * Get count.
     *
     * @return int|string|null
     */
    public function getCount()
    {
        return $this->_getData('count');
    }

    /**
     * Set count.
     *
     * @param int|string|null $count
     * @return self
     */
    public function setCount($count)
    {
        return $this->setData('count', $count);
    }

    /**
     * Get time from.
     *
     * @return string|null
     */
    public function getTimeFrom()
    {
        return $this->_getData('time_from');
    }

    /**
     * Set time from.
     *
     * @param string|null $timeFrom
     * @return self
     */
    public function setTimeFrom($timeFrom)
    {
        return $this->setData('time_from', $timeFrom);
    }

    /**
     * Get time to.
     *
     * @return string|null
     */
    public function getTimeTo()
    {
        return $this->_getData('time_to');
    }

    /**
     * Set time to.
     *
     * @param string|null $timeTo
     * @return self
     */
    public function setTimeTo($timeTo)
    {
        return $this->setData('time_to', $timeTo);
    }

    /**
     * Get address.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->_getData('address');
    }

    /**
     * Set address.
     *
     * @param string|null $address
     * @return self
     */
    public function setAddress($address)
    {
        return $this->setData('address', $address);
    }
}
