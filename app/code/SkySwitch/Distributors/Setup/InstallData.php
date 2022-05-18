<?php //phpcs:ignore
namespace SkySwitch\Distributors\Setup;

use Jenne\Service\Jenne;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use NtsDirect\Service\NtsDirect;
use Shipwire\Service\Shipwire;
use Teledynamics\Service\Teledynamics;
use Voip888\Service\Voip888;

class InstallData implements InstallDataInterface
{
    const DISTRIBUTORS = [ //phpcs:ignore
        ['name' => '888Voip', 'code' => 'voip', 'service_class' => Voip888::class],
        ['name' => 'Teledynamics', 'code' => 'teledynamics', 'service_class' => Teledynamics::class],
        ['name' => 'NTSDirect', 'code' => 'ntsdirect', 'service_class' => NtsDirect::class],
        ['name' => 'Jenne', 'code' => 'jenne', 'service_class' => Jenne::class],
        ['name' => 'Shipwire', 'code' => 'shipwire', 'service_class' => Shipwire::class]
    ];

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var phpcs:ignore
     */
    private $sales_setup_factory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    // @codingStandardsIgnoreStart
    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'distributors',
            [
                'group' => 'Distributors',
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Distributors',
                'input' => 'multiselect',
                'source' => 'SkySwitch\Distributors\Model\Config\Product\ExtensionOption',
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        foreach (self::DISTRIBUTORS as $distributor) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $distributor['code'] . '_sku',
                [
                    'group' => $distributor['name'],
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $distributor['name']. ' SKU',
                    'input' => 'text',
                    'source' => '',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'wysiwyg_enabled' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $distributor['code'] . '_price',
                [
                    'group' => $distributor['name'],
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $distributor['name']. ' Price',
                    'input' => 'text',
                    'source' => '',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'wysiwyg_enabled' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $distributor['code'] . '_stock',
                [
                    'group' => $distributor['name'],
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $distributor['name']. ' Stock',
                    'input' => 'text',
                    'source' => '',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'wysiwyg_enabled' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        $setup->endSetup();
    }
    // @codingStandardsIgnoreEnd
}
