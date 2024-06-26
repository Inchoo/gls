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
     * Get carrier config value.
     *
     * @param string $field
     * @param int|string|null $scopeCode
     * @return int|string|null
     */
    public function getConfigValue(string $field, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            "carriers/{$this->carrierCode}/{$field}",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get carrier config flag.
     *
     * @param string $field
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function getConfigFlag(string $field, $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            "carriers/{$this->carrierCode}/{$field}",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Is GLS carrier enabled.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isActive($scopeCode = null): bool
    {
        return $this->getConfigFlag('active', $scopeCode);
    }

    /**
     * Get GLS Client ID.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getClientId($scopeCode = null): string
    {
        return $this->getConfigValue('client_id', $scopeCode);
    }

    /**
     * Get API mode.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getMode($scopeCode = null): string
    {
        return (string)$this->getConfigValue('api_mode', $scopeCode);
    }

    /**
     * Get API username.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiUsername($scopeCode = null): string
    {
        return (string)$this->getConfigValue('api_username', $scopeCode);
    }

    /**
     * Get API password.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiPassword($scopeCode = null): string
    {
        return (string)$this->getConfigValue('api_password', $scopeCode);
    }

    /**
     * Get API country code.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiCountryCode($scopeCode = null): string
    {
        return (string)$this->getConfigValue('api_country', $scopeCode);
    }

    /**
     * Get SenderIdentityCardNumber.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getSenderIdentityCardNumber($scopeCode = null): string
    {
        return (string)$this->getConfigValue('sender_identity_card_number', $scopeCode);
    }

    /**
     * Get Content.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getContent($scopeCode = null): string
    {
        return (string)$this->getConfigValue('content', $scopeCode);
    }

    /**
     * Is debug log enabled.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isDebugEnabled($scopeCode = null): bool
    {
        return $this->getConfigFlag('debug', $scopeCode);
    }

    /**
     * Get Carrier title.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getCarrierTitle($scopeCode = null): string
    {
        return (string)$this->getConfigValue('title', $scopeCode);
    }

    /**
     * Get Client Reference.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getClientReference($scopeCode = null): string
    {
        return (string)$this->getConfigValue('client_reference', $scopeCode);
    }

    /**
     * Get Printer Type.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getPrinterType($scopeCode = null): string
    {
        return (string)$this->getConfigValue('printer_type', $scopeCode);
    }

    /**
     * Get Print Position.
     *
     * @param int|string|null $scopeCode
     * @return int
     */
    public function getPrintPosition($scopeCode = null): int
    {
        return (int)$this->getConfigValue('print_position', $scopeCode) ?: 1;
    }

    /**
     * Is enabled Guaranteed 24H Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledGuaranteed24hService($scopeCode = null): bool
    {
        return $this->getConfigFlag('guaranteed_24h', $scopeCode);
    }

    /**
     * Is enabled Express Delivery Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledExpressDeliveryService($scopeCode = null): bool
    {
        return $this->getConfigFlag('express_delivery', $scopeCode);
    }

    /**
     * Get Express Delivery code.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getExpressDeliveryServiceCode($scopeCode = null): string
    {
        return (string)$this->getConfigValue('express_delivery', $scopeCode);
    }

    /**
     * Is enabled Contact Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledContactService($scopeCode = null): bool
    {
        return $this->getConfigFlag('cs1', $scopeCode);
    }

    /**
     * Is enabled Flexible Delivery Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledFlexibleDeliveryService($scopeCode = null): bool
    {
        return $this->getConfigFlag('fds', $scopeCode);
    }

    /**
     * Is enabled Flexible Delivery SMS Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledFlexibleDeliverySmsService($scopeCode = null): bool
    {
        return $this->getConfigFlag('fss', $scopeCode);
    }

    /**
     * Is enabled SMS Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledSmsService($scopeCode = null): bool
    {
        return $this->getConfigFlag('sm1', $scopeCode);
    }

    /**
     * Get SMS Service text.
     *
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getSmsServiceText($scopeCode = null): string
    {
        return (string)$this->getConfigValue('sm1_text', $scopeCode);
    }

    /**
     * Is enabled SMS Pre-advice Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledSmsPreAdviceService($scopeCode = null): bool
    {
        return $this->getConfigFlag('sm2', $scopeCode);
    }

    /**
     * Is enabled Addressee Only Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledAddresseeOnlyService($scopeCode = null): bool
    {
        return $this->getConfigFlag('aos', $scopeCode);
    }

    /**
     * Is enabled Insurance Service.
     *
     * @param int|string|null $scopeCode
     * @return bool
     */
    public function isEnabledInsuranceService($scopeCode = null): bool
    {
        return $this->getConfigFlag('ins', $scopeCode);
    }

    /**
     * Get API URL.
     *
     * @param string $serviceName
     * @param int|string|null $scopeCode
     * @return string
     */
    public function getApiUrl(string $serviceName, $scopeCode = null): string
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
    public function getMapScriptUrl($scopeCode = null): string
    {
        switch ($this->getApiCountryCode($scopeCode)) {
            case 'CZ':
                return 'https://map.gls-czech.com/widget/gls-dpm.js';
            case 'HU':
                return 'https://map.gls-hungary.com/widget/gls-dpm.js';
            case 'RO':
                return 'https://map.gls-romania.com/widget/gls-dpm.js';
            case 'SI':
                return 'https://map.gls-slovenia.com/widget/gls-dpm.js';
            case 'SK':
                return 'https://map.gls-slovakia.com/widget/gls-dpm.js';
            case 'RS':
                return 'https://map.gls-serbia.com/widget/gls-dpm.js';
            default:
                return 'https://map.gls-croatia.com/widget/gls-dpm.js';
        }
    }

    /**
     * List of currently supported countries.
     *
     * @return string[]
     */
    public function getSupportedCountries(): array
    {
        $value = (string)$this->getConfigValue('supported_countries');
        return $value ? explode(',', $value) : [];
    }
}
