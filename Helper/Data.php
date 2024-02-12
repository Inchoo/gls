<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Helper;

class Data
{
    /**
     * GLS carrier configuration data.
     *
     * @param string $type
     * @param int|string|null $code
     * @return array|mixed|null
     */
    public function getConfigCode(string $type, $code = null)
    {
        $data = [
            'method' => [
                \GLSCroatia\Shipping\Model\Carrier::STANDARD_DELIVERY_METHOD => __('Delivery to Address'),
                \GLSCroatia\Shipping\Model\Carrier::PARCEL_SHOP_DELIVERY_METHOD => __('Delivery to Parcel Location')
            ],
            'country_calling_code' => [
                'CZ' => '+420',
                'HR' => '+385',
                'HU' => '+36',
                'RO' => '+40',
                'SI' => '+386',
                'SK' => '+421',
                'RS' => '+381',
            ],
            'country_currency_code' => [
                'CZ' => 'CZK',
                'HR' => 'EUR',
                'HU' => 'HUF',
                'RO' => 'RON',
                'SI' => 'EUR',
                'SK' => 'EUR',
                'RS' => 'RSD',
            ],
            'country_domestic_insurance' => [
                'CZ' => ['min' => 20000, 'max' => 100000], // CZK
                'HR' => ['min' => 165.9, 'max' => 1659.04], // EUR
                'HU' => ['min' => 50000, 'max' => 500000], // HUF
                'RO' => ['min' => 2000, 'max' => 7000], // RON
                'SI' => ['min' => 200, 'max' => 2000], // EUR
                'SK' => ['min' => 332, 'max' => 2655], // EUR
                'RS' => ['min' => 40000, 'max' => 200000] // RSD
            ],
            'country_export_insurance' => [
                'CZ' => ['min' => 20000, 'max' => 100000], // CZK
                'HR' => ['min' => 165.91, 'max' => 663.61], // EUR
                'HU' => ['min' => 50000, 'max' => 200000], // HUF
                'RO' => ['min' => 2000, 'max' => 7000], // RON
                'SI' => ['min' => 200, 'max' => 2000], // EUR
                'SK' => ['min' => 332, 'max' => 1000] // EUR
            ]
        ];

        if ($code === null) {
            return $data[$type] ?? [];
        }

        return $data[$type][$code] ?? null;
    }

    /**
     * Generate tracking URL.
     *
     * @param string $trackingNumber
     * @param string $countryCode
     * @return string
     */
    public function generateTrackingUrl(string $trackingNumber, string $countryCode = 'HR'): string
    {
        return "https://gls-group.eu/{$countryCode}/en/parcel-tracking/?match={$trackingNumber}";
    }
}
