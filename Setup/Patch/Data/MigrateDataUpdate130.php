<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Setup\Patch\Data;

class MigrateDataUpdate130 implements \Magento\Framework\Setup\Patch\DataPatchInterface
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
            \GLSCroatia\Shipping\Setup\Patch\Data\MigrateDataUpdate120::class
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
     * Migrate legacy GLS account data.
     *
     * @return $this
     */
    public function apply()
    {
        $fields = ['client_id', 'api_username', 'api_password', 'api_country'];

        $dataByScope = [];
        foreach ($fields as $field) {
            foreach ($this->getConfigData($field) as $row) {
                $scopeKey = "{$row['scope']}_{$row['scope_id']}";
                $dataByScope[$scopeKey][$field] = $row['value'];
            }
        }

        if (!$dataByScope) {
            return $this;
        }

        $defaultClientId = $dataByScope['default_0']['client_id'] ?? '';
        $defaultUsername = $dataByScope['default_0']['api_username'] ?? '';
        $defaultPassword = $dataByScope['default_0']['api_password'] ?? '';
        $defaultCountry = $dataByScope['default_0']['api_country'] ?? 'HR';
        unset($dataByScope['default_0']);

        $accountInsertData = $scopeConfigByAccount = [];
        if ($defaultClientId && $defaultUsername && $defaultPassword && $defaultCountry) {
            $accountKey = "{$defaultClientId}_{$defaultUsername}_{$defaultCountry}";
            $accountInsertData[$accountKey] = [
                'client_id' => $defaultClientId,
                'username' => $defaultUsername,
                'password' => $defaultPassword,
                'country_code' => $defaultCountry,
            ];
            $scopeConfigByAccount[$accountKey][] = ['scope' => 'default', 'scope_id' => 0];
        }

        foreach ($dataByScope as $scopeKey => $scopeData) {
            $clientId = $scopeData['client_id'] ?? $defaultClientId;
            $username = $scopeData['api_username'] ?? $defaultUsername;
            $password = $scopeData['api_password'] ?? $defaultPassword;
            $country = $scopeData['api_country'] ?? $defaultCountry;

            if ($clientId && $username && $password && $country) {
                $accountKey = "{$clientId}_{$username}_{$country}";

                $accountInsertData[$accountKey] = [
                    'client_id' => $clientId,
                    'username' => $username,
                    'password' => $password,
                    'country_code' => $country,
                ];

                $scopeConfigByAccount[$accountKey][] = array_combine(
                    ['scope', 'scope_id'],
                    explode('_', $scopeKey)
                );
            }
        }

        // clean up the "gls_shipping_account" table
        if ($accountInsertData) {
            $this->moduleDataSetup->getConnection()->delete(
                $this->moduleDataSetup->getTable('gls_shipping_account')
            );
        }

        // insert new data into the "gls_shipping_account" and "core_config_data" tables
        foreach ($accountInsertData as $accountKey => $accountRow) {
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('gls_shipping_account'),
                $accountRow
            );
            $entityId = $this->moduleDataSetup->getConnection()->lastInsertId(
                $this->moduleDataSetup->getTable('gls_shipping_account'),
                'entity_id'
            );

            foreach ($scopeConfigByAccount[$accountKey] as $configRow) {
                $configRow['path'] = 'carriers/gls/account_id';
                $configRow['value'] = $entityId;

                $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    $configRow,
                    ['value']
                );
            }
        }

        // clean up the "core_config_data" table
        foreach ($fields as $field) {
            $this->moduleDataSetup->getConnection()->delete(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path = ?' => "carriers/gls/{$field}"]
            );
        }

        return $this;
    }

    /**
     * Get field config data for all scopes.
     *
     * @param string $field
     * @return array
     */
    protected function getConfigData(string $field): array
    {
        $select = $this->moduleDataSetup->getConnection()->select();
        $select->from($this->moduleDataSetup->getTable('core_config_data'));
        $select->where('path = ?', "carriers/gls/{$field}");

        return $this->moduleDataSetup->getConnection()->fetchAll($select);
    }
}
