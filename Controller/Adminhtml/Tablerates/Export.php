<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Controller\Adminhtml\Tablerates;

class Export extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate
     */
    protected \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected \Magento\Framework\Filesystem $filesystem;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected \Magento\Framework\App\Response\Http\FileFactory $fileFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
    ) {
        $this->tablerateResource = $tablerateResource;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export GLS table rates for the current website.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $websiteId = (int)$this->storeManager->getWebsite($this->getRequest()->getParam('website'))->getId();

        $directory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $directory->create('export');

        $name = md5(microtime()); // phpcs:ignore
        $filePath = "export/{$name}.csv";

        $stream = $directory->openFile($filePath, 'w+');
        $stream->lock();

        // CSV header
        $stream->writeCsv($this->tablerateResource->getCsvHeaderColumns());

        $currentPage = 1;
        while ($rates = $this->tablerateResource->exportRates($websiteId, $currentPage++)) {
            foreach ($rates as $rate) {
                $stream->writeCsv($rate);
            }
        }

        $stream->unlock();
        $stream->close();

        return $this->fileFactory->create(
            "gls_tablerates_{$websiteId}.csv",
            ['type' => 'filename', 'value' => $filePath, 'rm' => true],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}
