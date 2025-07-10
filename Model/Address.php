<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

class Address extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'gls_address';

    /**
     * Initialize model object.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\GLSCroatia\Shipping\Model\ResourceModel\Address::class);
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
     * Get label.
     *
     * @return string|null
     */
    public function getLabel(): string|null
    {
        return $this->_getData('label');
    }

    /**
     * Set label.
     *
     * @param string|null $label
     * @return self
     */
    public function setLabel(string|null $label): self
    {
        return $this->setData('label', $label);
    }

    /**
     * Get country ID.
     *
     * @return string|null
     */
    public function getCountryId(): string|null
    {
        return $this->_getData('country_id');
    }

    /**
     * Set country ID.
     *
     * @param string|null $countryId
     * @return self
     */
    public function setCountryId(string|null $countryId): self
    {
        return $this->setData('country_id', $countryId);
    }

    /**
     * Get region ID.
     *
     * @return int|string|null
     */
    public function getRegionId(): int|string|null
    {
        return $this->_getData('region_id');
    }

    /**
     * Set region ID.
     *
     * @param int|string|null $regionId
     * @return self
     */
    public function setRegionId(int|string|null $regionId): self
    {
        return $this->setData('region_id', $regionId);
    }

    /**
     * Get region.
     *
     * @return string|null
     */
    public function getRegion(): string|null
    {
        return $this->_getData('region');
    }

    /**
     * Set region.
     *
     * @param string|null $region
     * @return self
     */
    public function setRegion(string|null $region): self
    {
        return $this->setData('region', $region);
    }

    /**
     * Get postcode.
     *
     * @return string|null
     */
    public function getPostcode(): string|null
    {
        return $this->_getData('postcode');
    }

    /**
     * Set postcode.
     *
     * @param string|null $postcode
     * @return self
     */
    public function setPostcode(string|null $postcode): self
    {
        return $this->setData('postcode', $postcode);
    }

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity(): string|null
    {
        return $this->_getData('city');
    }

    /**
     * Set city.
     *
     * @param string|null $city
     * @return self
     */
    public function setCity(string|null $city): self
    {
        return $this->setData('city', $city);
    }

    /**
     * Get street.
     *
     * @return string|null
     */
    public function getStreet(): string|null
    {
        return $this->_getData('street');
    }

    /**
     * Set street.
     *
     * @param string|null $street
     * @return self
     */
    public function setStreet(string|null $street): self
    {
        return $this->setData('street', $street);
    }

    /**
     * Get street line 2.
     *
     * @return string|null
     */
    public function getStreetLine2(): string|null
    {
        return $this->_getData('street_line2');
    }

    /**
     * Set street line 2.
     *
     * @param string|null $streetLine2
     * @return self
     */
    public function setStreetLine2(string|null $streetLine2): self
    {
        return $this->setData('street_line2', $streetLine2);
    }
}
