<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\Config\Comment;

class Account implements \Magento\Config\Model\Config\CommentInterface
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
     * Generate account field comment.
     *
     * @param string $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        return __(
            'This is required for API usage. <a href="%1">Create account</a>.',
            $this->url->getUrl('gls/account/edit')
        );
    }
}
