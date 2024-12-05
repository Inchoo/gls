<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Block\Adminhtml\Form\Field;

class Export extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $url;

    /**
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     * @param \Magento\Framework\View\Helper\SecureHtmlRenderer|null $secureRenderer
     * @param \Magento\Framework\Math\Random|null $random
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        array $data = [],
        ?\Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer = null,
        ?\Magento\Framework\Math\Random $random = null
    ) {
        $this->url = $url;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
    }

    /**
     * GLS table rates export button.
     *
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );

        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];
        $url = $this->url->getUrl("gls/tablerates/export", $params);

        $data = [
            'label' => __('Export CSV'),
            'onclick' => "setLocation('{$url}')",
            'class' => '',
        ];

        return $buttonBlock->setData($data)->toHtml();
    }
}
