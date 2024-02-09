<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier\ShipmentRequest;

use Magento\Sales\Model\Order\Shipment;

class Insurance
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

    /**
     * @var \GLSCroatia\Shipping\Model\Config
     */
    protected \GLSCroatia\Shipping\Model\Config $config;

    /**
     * @var \Magento\Framework\Math\FloatComparator
     */
    protected \Magento\Framework\Math\FloatComparator $floatComparator;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected \Magento\Directory\Model\CurrencyFactory $currencyFactory;

    /**
     * @param \GLSCroatia\Shipping\Helper\Data $dataHelper
     * @param \GLSCroatia\Shipping\Model\Config $config
     * @param \Magento\Framework\Math\FloatComparator $floatComparator
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \GLSCroatia\Shipping\Model\Config $config,
        \Magento\Framework\Math\FloatComparator $floatComparator,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->config = $config;
        $this->floatComparator = $floatComparator;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Check if insurance is allowed for package value.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param string $originCountry
     * @param string $destinationCountry
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAllowed(Shipment $shipment, string $originCountry, string $destinationCountry): bool
    {
        if (!$originCurrencyCode = $this->dataHelper->getConfigCode('country_currency_code', $originCountry)) {
            return false;
        }

        $type = $originCountry === $destinationCountry ? 'country_domestic_insurance' : 'country_export_insurance';
        if (!$minMax = $this->dataHelper->getConfigCode($type, $originCountry)) {
            return false;
        }

        $currencyModel = $this->currencyFactory->create()->load($shipment->getOrder()->getOrderCurrencyCode());
        // convert package value amount from order currency to target currency
        $packageValue = (float)$currencyModel->convert($this->calculateValue($shipment), $originCurrencyCode);

        return $this->floatComparator->greaterThanOrEqual($packageValue, (float)$minMax['min'])
            && $this->floatComparator->greaterThanOrEqual((float)$minMax['max'], $packageValue);
    }

    /**
     * Calculate shipment value.
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return float
     */
    public function calculateValue(Shipment $shipment): float
    {
        $value = 0;
        foreach ($shipment->getPackages() as $packageData) {
            $value += $packageData['params']['customs_value'] ?? 0;
        }

        return $value;
    }
}
