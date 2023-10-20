<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

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
     * List of currently supported countries.
     *
     * @return string[]
     */
    public function getSupportedCountries(): array
    {
        return [
            'cz' => 'CZ',
            'hr' => 'HR',
            'hu' => 'HU',
            'ro' => 'RO',
            'rs' => 'RS',
            'si' => 'SI',
            'sk' => 'SK',
        ];
    }
}
