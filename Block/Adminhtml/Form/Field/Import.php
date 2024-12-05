<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Block\Adminhtml\Form\Field;

class Import extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * GLS table rates import button.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setType('file');
    }
}
