<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier\ShipmentRequest;

class ExpressDelivery
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @var \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem
     */
    protected \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem $filesystem;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem $filesystem
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem $filesystem
    ) {
        $this->dataHelper = $dataHelper;
        $this->filesystem = $filesystem;
    }

    /**
     * Check if express delivery is allowed for country/postcode.
     *
     * @see \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service::isExpressDeliveryAllowed()
     *
     * @param string $expressDeliverCode
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isAllowed(string $expressDeliverCode, \Magento\Framework\DataObject $request): bool
    {
        if (!$this->isAllowedShipmentRequest($request)) {
            return false;
        }

        $columnKeys = ['T12' => 2, 'T09' => 3, 'T10' => 4];
        $columnKey = $columnKeys[$expressDeliverCode] ?? null;
        if ($columnKey === null) {
            return false; // invalid express delivery code
        }

        try {
            $file = $this->filesystem->openFile((string)$request->getShipperAddressCountryCode());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }

        $headerRow = $file->readCsv();
        if (count($headerRow) !== 5) {
            return false; // invalid CSV
        }

        $postcode = (string)$request->getRecipientAddressPostalCode();

        while ($row = $file->readCsv()) {
            if ((string)$row[1] === $postcode) {
                return strtolower((string)$row[$columnKey]) === 'x';
            }
        }

        return false;
    }

    /**
     * Check if express delivery is allowed for country/postcode.
     *
     * @see \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service::isExpressDeliveryAllowed()
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return bool
     */
    public function isAllowedShipmentRequest(\Magento\Framework\DataObject $request): bool
    {
        $originCountryCode = (string)$request->getShipperAddressCountryCode();
        $destinationCountryCode = (string)$request->getRecipientAddressCountryCode();

        return $originCountryCode === $destinationCountryCode
            && !$this->dataHelper->isLockerShopDeliveryMethod($request->getShippingMethod());
    }
}
