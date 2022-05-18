<?php //phpcs:ignore
namespace SkySwitch\Distributors\Setup;

use Jenne\Service\Jenne;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use NtsDirect\Service\NtsDirect;
use Shipwire\Service\Shipwire;
use Teledynamics\Service\Teledynamics;
use Voip888\Service\Voip888;

class InstallSchema implements InstallSchemaInterface
{
    const DISTRIBUTORS = [ //phpcs:ignore
        ['name' => '888Voip', 'code' => 'voip', 'service_class' => Voip888::class],
        ['name' => 'Teledynamics', 'code' => 'teledynamics', 'service_class' => Teledynamics::class],
        ['name' => 'NTSDirect', 'code' => 'ntsdirect', 'service_class' => NtsDirect::class],
        ['name' => 'Jenne', 'code' => 'jenne', 'service_class' => Jenne::class],
        ['name' => 'Shipwire', 'code' => 'shipwire', 'service_class' => Shipwire::class]
    ];

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table_name = $setup->getTable('skyswitch_distributors');

        if ($setup->getConnection()->isTableExists($table_name) != true) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('skyswitch_distributors'))
                ->addColumn(
                    'distributor_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Name'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Code'
                )
                ->addColumn(
                    'service_class',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Service Class'
                )
                ->setComment('SkySwitch Distributors - Distributors');

            $installer->getConnection()->createTable($table);

            $setup->getConnection()->insertMultiple($table_name, self::DISTRIBUTORS);
        }

        $table_name = $setup->getTable('skyswitch_product_distributor');

        if ($setup->getConnection()->isTableExists($table_name) != true) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('skyswitch_product_distributor'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'distributor_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Distributor Id'
                )
                ->addColumn(
                    'margin_type',
                    Table::TYPE_TEXT,
                    55,
                    ['default' => 'fixed'],
                    'Margin Type'
                )
                ->addColumn(
                    'margin_value',
                    Table::TYPE_FLOAT,
                    null,
                    ['default' => 0],
                    'Margin Value'
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'skyswitch_product_distributor',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'skyswitch_product_distributor',
                        'distributor_id',
                        'skyswitch_distributors',
                        'distributor_id'
                    ),
                    'distributor_id',
                    $installer->getTable('skyswitch_distributors'),
                    'distributor_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Relation between distributors and products');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
