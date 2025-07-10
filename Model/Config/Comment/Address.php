<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Comment;

class Address implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $url;

    /**
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url
    ) {
        $this->url = $url;
    }

    /**
     * Generate address field comment.
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        return __(
            'If not selected, the Shipping Settings Origin address will be used. <a href="%1">Create address</a>.',
            $this->url->getUrl('gls/address/edit')
        );
    }
}
