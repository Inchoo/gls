<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Backend;

class Tablerate extends \Magento\Framework\App\Config\Value
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
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory;

    /**
     * @param \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \GLSCroatia\Shipping\Model\ResourceModel\Carrier\Tablerate $tablerateResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->tablerateResource = $tablerateResource;
        $this->storeManager = $storeManager;
        $this->fileReadFactory = $fileReadFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare the CSV file path and configuration value.
     *
     * @return self
     */
    public function beforeSave()
    {
        $this->setData('file_path', $this->getData('value/tmp_name'));

        if ($this->getData('value/name')) {
            $this->setValue((string)time());
        } else {
            $this->setValue($this->getOldValue());
        }

        return parent::beforeSave();
    }

    /**
     * Import the GLS table rates data.
     *
     * @return self
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        if (!$filePath = $this->getData('file_path')) {
            return parent::afterSave();
        }

        $file = $this->fileReadFactory->create($filePath, \Magento\Framework\Filesystem\DriverPool::FILE);

        if (!$columnNames = $file->readCsv()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The header row is missing from the GLS tablerates CSV file.')
            );
        }

        if ($missingColumns = array_diff($this->tablerateResource->getCsvHeaderColumns(), $columnNames)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The GLS tablerates CSV file is missing the following columns: %1', implode(', ', $missingColumns))
            );
        }

        $websiteId = (int)$this->storeManager->getWebsite($this->getScopeId())->getId();
        $this->tablerateResource->deleteRates($websiteId);

        $count = 0;
        $rates = [];
        while ($row = $file->readCsv()) {
            $dataRow = array_combine($columnNames, $row);
            $rates[] = [
                'website_id'   => $websiteId,
                'country_code' => (string)$dataRow['country_code'] ?: '*',
                'region_code'  => (string)$dataRow['region_code'] ?: '*',
                'postcode'     => (string)$dataRow['postcode'] ?: '*',
                'weight'       => (float)$dataRow['weight'],
                'subtotal'     => (float)$dataRow['subtotal'],
                'quantity'     => (float)$dataRow['quantity'],
                'price'        => (float)$dataRow['price']
            ];

            if (++$count >= 200) {
                $this->tablerateResource->importRates($rates);
                $count = 0;
                $rates = [];
            }
        }

        if ($rates) {
            $this->tablerateResource->importRates($rates);
        }

        return parent::afterSave();
    }
}
