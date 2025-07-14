<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Source;

class Website implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Option source for websites.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $result = [];
        foreach ($this->storeManager->getWebsites(true) as $website) {
            $websiteName = (int)$website->getId() === 0 ? "{$website->getName()} (Default)" : $website->getName();
            $result[] = ['value' => $website->getId(), 'label' => $websiteName];
        }

        usort($result, static function ($a, $b) {
            return $a['value'] <=> $b['value'];
        });

        return $this->options = $result;
    }
}
