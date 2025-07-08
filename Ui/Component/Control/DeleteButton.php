<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Ui\Component\Control;

class DeleteButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $url;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected \Magento\Framework\App\RequestInterface $request;

    /**
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->url = $url;
        $this->request = $request;
    }

    /**
     * Retrieve button-specified settings.
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];

        if ($id = $this->request->getParam('id')) {
            $message = __('Are you sure you want to delete this record?');
            $url = $this->url->getUrl('*/*/delete');
            $data = "{data: {id: {$id}}}";

            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf("deleteConfirm('%s', '%s', %s)", $message, $url, $data)
            ];
        }

        return $data;
    }
}
