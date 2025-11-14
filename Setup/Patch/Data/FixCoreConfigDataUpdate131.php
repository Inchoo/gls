<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Setup\Patch\Data;

class FixCoreConfigDataUpdate131 implements \Magento\Framework\Setup\Patch\DataPatchInterface
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
        return [
            \GLSCroatia\Shipping\Setup\Patch\Data\MigrateDataUpdate130::class
        ];
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
     * Fix the "standard_method_sallowspecific" and "standard_method_specificcountry" config paths.
     *
     * @return $this
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['path' => 'carriers/gls/standard_method_sallowspecific'],
            ['path = ?' => 'carriers/gls/standard_sallowspecific']
        );

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['path' => 'carriers/gls/standard_method_specificcountry'],
            ['path = ?' => 'carriers/gls/standard_specificcountry']
        );

        return $this;
    }
}
