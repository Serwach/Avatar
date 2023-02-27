<?php

declare (strict_types=1);

namespace Monogo\Task\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallAvatarAttribute implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private ModuleDataSetupInterface $moduleDataSetup;

    /** @var EavSetupFactory */
    private EavSetupFactory $eavSetupFactory;

    /** @var Config */
    private Config $eavConfig;

    /** @var Attribute */
    private Attribute $attributeResource;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     * @param Attribute $attributeResource
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        Attribute $attributeResource,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return void
     * @throws AlreadyExistsException|LocalizedException
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            Customer::ENTITY,
            'avatar',
            [
                'type' => 'text',
                'label' => 'Avatar',
                'input' => 'file',
                'source' => '',
                'required' => false,
                'visible' => true,
                'position' => 200,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true
            ]
        );

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        try {
            $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'avatar');
            $attribute->setData('attribute_set_id', $attributeSetId);
            $attribute->setData('attribute_group_id', $attributeGroupId);
        } catch (AlreadyExistsException|LocalizedException $e) {
            return;
        }

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_edit',
            'customer_account_create'
        ]);

        $this->attributeResource->save($attribute);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
