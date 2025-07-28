<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ExpressDelivery;

use Magento\Framework\Filesystem\File\ReadInterface;

class Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected \Magento\Framework\Module\Dir $moduleDir;

    /**
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
     * @param \Magento\Framework\Module\Dir $moduleDir
     */
    public function __construct(
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory,
        \Magento\Framework\Module\Dir $moduleDir
    ) {
        $this->fileReadFactory = $fileReadFactory;
        $this->moduleDir = $moduleDir;
    }

    /**
     * Open express delivery CSV file.
     *
     * @param string $countryCode
     * @param string $dirPath
     * @return ReadInterface
     */
    public function openFile(string $countryCode, string $dirPath = ''): ReadInterface
    {
        $dirPath = $dirPath ?: $this->getDefaultDirPath();
        $fileName = strtolower($countryCode) . '.csv';
        $filePath = $dirPath . DIRECTORY_SEPARATOR . $fileName;

        return $this->fileReadFactory->create(
            $filePath,
            \Magento\Framework\Filesystem\DriverPool::FILE
        );
    }

    /**
     * Get default "express_delivery" directory path in module.
     *
     * @return string
     */
    protected function getDefaultDirPath(): string
    {
        return $this->moduleDir->getDir('GLSCroatia_Shipping') . DIRECTORY_SEPARATOR . 'express_delivery';
    }
}
