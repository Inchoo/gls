<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ExpressDelivery;

class Checker
{
    /**
     * @var \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem
     */
    protected \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem $filesystem;

    /**
     * @param \GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem $filesystem
     */
    public function __construct(\GLSCroatia\Shipping\Model\ExpressDelivery\Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Check if express delivery is allowed for country/postcode.
     *
     * @param string $expressDeliverCode
     * @param string $originCountryCode
     * @param string $destinationCountryCode
     * @param string $postcode
     * @return bool
     */
    public function isAllowed(
        string $expressDeliverCode,
        string $originCountryCode,
        string $destinationCountryCode,
        string $postcode
    ): bool {
        if ($originCountryCode !== $destinationCountryCode) {
            return false;
        }

        $columnKeys = ['T12' => 2, 'T09' => 3, 'T10' => 4];
        $columnKey = $columnKeys[$expressDeliverCode] ?? null;
        if ($columnKey === null) {
            return false; // invalid express delivery code
        }

        try {
            $file = $this->filesystem->openFile($originCountryCode);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }

        $headerRow = $file->readCsv();
        if (count($headerRow) !== 5) {
            return false; // invalid CSV
        }

        while ($row = $file->readCsv()) {
            if ((string)$row[1] === $postcode) {
                return strtolower((string)$row[$columnKey]) === 'x';
            }
        }

        return false;
    }
}
