<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Carrier\ShipmentRequest;

class Insurance
{
    /**
     * @var \GLSCroatia\Shipping\Helper\Data
     */
    protected \GLSCroatia\Shipping\Helper\Data $dataHelper;

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
     * @param \Magento\Framework\Math\FloatComparator $floatComparator
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \GLSCroatia\Shipping\Helper\Data $dataHelper,
        \Magento\Framework\Math\FloatComparator $floatComparator,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->floatComparator = $floatComparator;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Check if insurance is allowed for package value.
     *
     * @see \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service::isInsuranceAllowed()
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param string $originCountry
     * @param string $destinationCountry
     * @return bool
     */
    public function isAllowed(
        \Magento\Sales\Model\Order\Shipment $shipment,
        string $originCountry,
        string $destinationCountry
    ): bool {
        if (!$originCurrencyCode = $this->dataHelper->getConfigCode('country_currency_code', $originCountry)) {
            return false;
        }

        $type = $originCountry === $destinationCountry ? 'country_domestic_insurance' : 'country_export_insurance';
        if (!$minMax = $this->dataHelper->getConfigCode($type, $originCountry)) {
            return false;
        }

        try {
            $currencyModel = $this->currencyFactory->create()->load($shipment->getOrder()->getOrderCurrencyCode());
            // convert package value amount from order currency to target currency
            $packageValue = (float)$currencyModel->convert($this->calculateValue($shipment), $originCurrencyCode);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }

        return $this->floatComparator->greaterThanOrEqual($packageValue, (float)$minMax['min'])
            && $this->floatComparator->greaterThanOrEqual((float)$minMax['max'], $packageValue);
    }

    /**
     * Calculate shipment value.
     *
     * @see \GLSCroatia\Shipping\Model\Carrier\ShipmentRequest\Service::calculateInsuranceValue()
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return float
     */
    public function calculateValue(\Magento\Sales\Model\Order\Shipment $shipment): float
    {
        $value = 0;
        foreach ($shipment->getPackages() as $packageData) {
            $value += $packageData['params']['customs_value'] ?? 0;
        }

        return $value;
    }
}
