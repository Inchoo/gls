<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier\ShipmentRequest;

class AddressSwitcher
{
    /**
     * @var \GLSCroatia\Shipping\Model\AddressRepository
     */
    protected \GLSCroatia\Shipping\Model\AddressRepository $addressRepository;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected \Magento\Directory\Model\RegionFactory $regionFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\AddressRepository $addressRepository
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\AddressRepository $addressRepository,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Switch shipper address.
     *
     * @param int $addressId
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    public function switchShipperAddress(
        int $addressId,
        \Magento\Framework\DataObject $request
    ): \Magento\Framework\DataObject {
        try {
            $address = $this->addressRepository->get($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $request;
        }

        $addressStreet = $streetLine1 = $address->getStreet();
        if ($streetLine2 = $address->getStreetLine2()) {
            $addressStreet .= " {$streetLine2}";
        }

        if ($regionId = $address->getRegionId()) {
            $regionCode = $this->regionFactory->create()->load($regionId)->getCode();
        } else {
            $regionCode = $address->getRegion();
        }

        if ($company = $address->getCompany()) {
            $request->setShipperContactCompanyName($company);
        }
        if ($phoneNumber = $address->getPhoneNumber()) {
            $phoneNumber = is_string($phoneNumber) ? preg_replace('/[\s_\-()]+/', '', $phoneNumber) : '';
            $request->setShipperContactPhoneNumber($phoneNumber);
        }
        $request->setShipperAddressStreet($addressStreet);
        $request->setShipperAddressStreet1($streetLine1);
        $request->setShipperAddressStreet2($streetLine2);
        $request->setShipperAddressCity($address->getCity());
        $request->setShipperAddressStateOrProvinceCode($regionCode);
        $request->setShipperAddressPostalCode($address->getPostcode());
        $request->setShipperAddressCountryCode($address->getCountryCode());

        return $request;
    }
}
