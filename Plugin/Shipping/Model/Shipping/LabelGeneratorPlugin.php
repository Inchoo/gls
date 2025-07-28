<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Shipping\Model\Shipping;

class LabelGeneratorPlugin
{
    /**
     * Add custom GLS data to the shipment before generating the label.
     *
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $subject
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array
     */
    public function beforeCreate(
        \Magento\Shipping\Model\Shipping\LabelGenerator $subject,
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Magento\Framework\App\RequestInterface $request
    ): array {
        if ($glsData = $request->getParam('gls')) {
            $shipment->setData('gls', $glsData);
        }

        return [$shipment, $request];
    }
}
