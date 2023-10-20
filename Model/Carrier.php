<?php

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    public const CODE = 'gls';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Collect and get GLS rates.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Framework\DataObject|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive() || !$allowedMethods = $this->getAllowedMethods()) {
            return false;
        }

        $result = $this->_rateFactory->create();

        foreach ($allowedMethods as $methodCode => $methodTitle) {
            $method = $this->_rateMethodFactory->create();

            $price = $this->getConfigData("{$methodCode}_method_price") ?: 0;

            $method->setData([
                'carrier'       => $this->getCarrierCode(),
                'carrier_title' => $this->getConfigData('title'),
                'method'        => $methodCode,
                'method_title'  => $this->getConfigData("{$methodCode}_method_name") ?: $methodTitle,
                'price'         => (float)$price,
                'cost'          => (float)$price
            ]);

            $result->append($method);
        }

        return $result;
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        // TODO: Implement _doShipmentRequest() method.
    }

    /**
     * Get allowed GLS shipping methods.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));

        $result = [];
        foreach ($allowedMethods as $code) {
            $result[$code] = $this->getCode('method', $code) ?: 'GLS';
        }

        return $result;
    }

    /**
     * Zip code is not required.
     *
     * @param string|null $countryId
     * @return false
     */
    public function isZipCodeRequired($countryId = null)
    {
        return false;
    }

    /**
     * Get GLS carrier configuration data.
     *
     * @param string $type
     * @param int|string|null $code
     * @return mixed
     */
    public function getCode(string $type, int|string $code = null): mixed
    {
        $data = [
            'method' => [
                'hd' => __('Home Delivery'),
                'oohd' => __('Out of Home Delivery')
            ]
        ];

        if ($code === null) {
            return $data[$type] ?? [];
        }

        return $data[$type][$code] ?? null;
    }
}
