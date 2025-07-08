<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Model\ResourceModel;

class Account extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected \Magento\Framework\Encryption\EncryptorInterface $encryptor;

    /**
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        ?string $connectionName = null
    ) {
        $this->encryptor = $encryptor;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gls_shipping_account', 'entity_id');
    }

    /**
     * Perform actions after account load.
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function _afterLoad(\Magento\Framework\DataObject $object)
    {
        parent::_afterLoad($object);

        if ($password = $object->getData('password')) {
            $object->setData('password', $this->encryptor->decrypt($password));
        }
    }

    /**
     * Perform actions before account save.
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function _beforeSave(\Magento\Framework\DataObject $object)
    {
        parent::_beforeSave($object);

        if ($password = $object->getData('password')) {
            $object->setData('password', $this->encryptor->encrypt($password));
        }
    }
}
