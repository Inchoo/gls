<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Plugin\Shipping\Block\Adminhtml\Order;

class PackagingPlugin
{
    /**
     * Include GLS options to "packaging/popup_content" template.
     *
     * @param \Magento\Shipping\Block\Adminhtml\Order\Packaging $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(\Magento\Shipping\Block\Adminhtml\Order\Packaging $subject, string $result): string
    {
        if (!$glsOptionsBlock = $subject->getChildBlock('gls_options')) {
            return $result;
        }

        $position = strpos($result, '<div id="packages_content"');
        if ($position !== false) {
            $result = substr_replace($result, $glsOptionsBlock->toHtml(), $position, 0);
        }

        return $result;
    }
}
