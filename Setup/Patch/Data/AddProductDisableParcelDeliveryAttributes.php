<?php
/**
 * Copyright (c) 2023-present GLS Croatia. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * @author Inchoo (https://inchoo.net)
 */

declare(strict_types=1);

namespace GLSCroatia\Shipping\Setup\Patch\Data;

class AddProductDisableParcelDeliveryAttributes implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Create the "disable_parcel_locker_delivery" and "disable_parcel_shop_delivery" product attributes.
     *
     * @return self
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $entityType = \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE;

        $eavSetup->addAttribute($entityType, 'disable_parcel_locker_delivery', [
            'label' => 'Disable GLS Parcel Locker Delivery',
            'type' => 'int',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'default' => 0,
            'required' => 0,
            'user_defined' => 1,
            'is_used_in_grid' => 1,
            'is_filterable_in_grid' => 1,
        ]);

        $eavSetup->addAttribute($entityType, 'disable_parcel_shop_delivery', [
            'label' => 'Disable GLS Parcel Shop Delivery',
            'type' => 'int',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'default' => 0,
            'required' => 0,
            'user_defined' => 1,
            'is_used_in_grid' => 1,
            'is_filterable_in_grid' => 1,
        ]);

        foreach ($eavSetup->getAllAttributeSetIds($entityType) as $setId) {
            $defaultGroupId = $eavSetup->getDefaultAttributeGroupId($entityType, $setId);
            $eavSetup->addAttributeToSet($entityType, $setId, $defaultGroupId, 'disable_parcel_locker_delivery');
            $eavSetup->addAttributeToSet($entityType, $setId, $defaultGroupId, 'disable_parcel_shop_delivery');
        }

        return $this;
    }
}
