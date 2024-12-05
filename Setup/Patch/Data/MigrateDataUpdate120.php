<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Setup\Patch\Data;

class MigrateDataUpdate120 implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Migrate legacy PSD configuration data.
     *
     * @return $this
     */
    public function apply()
    {
        $this->updateAllowedMethods();
        $this->updateParcelLockerShopPrices();
        $this->updateParcelLockerShopSpecificCountry();

        return $this;
    }

    /**
     * PSD allowed methods.
     *
     * @return void
     */
    protected function updateAllowedMethods(): void
    {
        $select = $this->moduleDataSetup->getConnection()->select();
        $select->from($this->moduleDataSetup->getTable('core_config_data'));
        $select->where('path = ?', 'carriers/gls/allowed_methods');

        foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $row) {
            $allowedMethods = $row['value'] ? explode(',', $row['value']) : [];
            if (!in_array('psd', $allowedMethods, true)) {
                continue;
            }

            $allowedMethods = array_filter($allowedMethods, static function ($method) {
                return $method !== 'psd';
            });
            if (!in_array('locker', $allowedMethods, true)) {
                $allowedMethods[] = 'locker';
            }
            if (!in_array('shop', $allowedMethods, true)) {
                $allowedMethods[] = 'shop';
            }

            $this->moduleDataSetup->getConnection()->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['value' => implode(',', $allowedMethods)],
                ['config_id = ?' => $row['config_id']]
            );
        }
    }

    /**
     * PSD price.
     *
     * @return void
     */
    protected function updateParcelLockerShopPrices(): void
    {
        $select = $this->moduleDataSetup->getConnection()->select();
        $select->from($this->moduleDataSetup->getTable('core_config_data'));
        $select->where('path = ?', 'carriers/gls/psd_method_price');

        foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $row) {
            unset($row['config_id'], $row['updated_at']);

            $row['path'] = 'carriers/gls/locker_method_price';
            try {
                $this->moduleDataSetup->getConnection()->insert(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    $row
                );
            } catch (\Exception $e) { // phpcs:ignore
            }

            $row['path'] = 'carriers/gls/shop_method_price';
            try {
                $this->moduleDataSetup->getConnection()->insert(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    $row
                );
            } catch (\Exception $e) { // phpcs:ignore
            }
        }
    }

    /**
     * PSD specific country.
     *
     * @return void
     */
    protected function updateParcelLockerShopSpecificCountry(): void
    {
        $select = $this->moduleDataSetup->getConnection()->select();
        $select->from($this->moduleDataSetup->getTable('core_config_data'));
        $select->where('path = ?', 'carriers/gls/psd_specificcountry');

        foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $row) {
            unset($row['config_id'], $row['updated_at']);

            $row['path'] = 'carriers/gls/locker_method_specificcountry';
            try {
                $this->moduleDataSetup->getConnection()->insert(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    $row
                );
            } catch (\Exception $e) { // phpcs:ignore
            }

            $row['path'] = 'carriers/gls/shop_method_specificcountry';
            try {
                $this->moduleDataSetup->getConnection()->insert(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    $row
                );
            } catch (\Exception $e) { // phpcs:ignore
            }
        }
    }
}
