<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

use GLSCroatia\Shipping\Model\Config\Source\Mode;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @var string
     */
    protected string $carrierCode;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $carrierCode
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        string $carrierCode = \GLSCroatia\Shipping\Model\Carrier::CODE
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->carrierCode = $carrierCode;
    }

    /**
     * Is GLS carrier enabled.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isActive(int|string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            "carriers/{$this->carrierCode}/active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get GLS Client ID.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getClientId(int|string $scopeCode = null): string
    {
        return (string)$this->scopeConfig->getValue(
            "carriers/{$this->carrierCode}/client_id",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get API mode.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getMode(int|string $scopeCode = null): string
    {
        return (string)$this->scopeConfig->getValue(
            "carriers/{$this->carrierCode}/api_mode",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get API username.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiUsername(int|string $scopeCode = null): string
    {
        return (string)$this->scopeConfig->getValue(
            "carriers/{$this->carrierCode}/api_username",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get API password.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiPassword(int|string $scopeCode = null): string
    {
        return (string)$this->scopeConfig->getValue(
            "carriers/{$this->carrierCode}/api_password",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get API country code.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiCountryCode(int|string $scopeCode = null): string
    {
        return (string)$this->scopeConfig->getValue(
            "carriers/{$this->carrierCode}/api_country",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Is debug log enabled.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isDebugEnabled(int|string $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            "carriers/{$this->carrierCode}/debug",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get API URL.
     *
     * @param string $serviceName
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiUrl(string $serviceName, int|string $scopeCode = null): string
    {
        $countryCode = $this->getApiCountryCode($scopeCode);
        if (!$countryCode || !in_array($countryCode, $this->getSupportedCountries(), true)) {
            return '';
        }

        $domain = $this->getMode($scopeCode) === Mode::PRODUCTION ? 'mygls' : 'test.mygls';
        $topLevelDomain = strtolower($countryCode);

        return "https://api.{$domain}.{$topLevelDomain}/{$serviceName}.svc/";
    }

    /**
     * Get GLS map script URL.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getMapScriptUrl(int|string $scopeCode = null): string
    {
        return match ($this->getApiCountryCode($scopeCode)) {
            'CZ' => 'https://map.gls-czech.com/widget/gls-dpm.js',
            'HU' => 'https://map.gls-hungary.com/widget/gls-dpm.js',
            'RO' => 'https://map.gls-romania.com/widget/gls-dpm.js',
            'SI' => 'https://map.gls-slovenia.com/widget/gls-dpm.js',
            'SK' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
            'RS' => 'https://map.gls-serbia.com/widget/gls-dpm.js',
            default => 'https://map.gls-croatia.com/widget/gls-dpm.js',
        };
    }

    /**
     * List of currently supported countries.
     *
     * @return string[]
     */
    public function getSupportedCountries(): array
    {
        $value = (string)$this->scopeConfig->getValue("carriers/{$this->carrierCode}/supported_countries");
        return $value ? explode(',', $value) : [];
    }
}
