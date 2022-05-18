<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Amasty\CompanyAccount\Api\Data\PermissionInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createCompanyTable($setup);
        $this->createRoleTable($setup);
        $this->createCustomerTable($setup);
        $this->createPermissionTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createCompanyTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(CompanyInterface::TABLE_NAME))
            ->addColumn(
                CompanyInterface::COMPANY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Company Id'
            )
            ->addColumn(
                CompanyInterface::COMPANY_NAME,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Company Name'
            )
            ->addColumn(
                CompanyInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false, 'unsigned' => true, 'identity' => false],
                'Status'
            )
            ->addColumn(
                CompanyInterface::LEGAL_NAME,
                Table::TYPE_TEXT,
                80,
                ['nullable' => true],
                'Legal Name'
            )
            ->addColumn(
                CompanyInterface::COMPANY_EMAIL,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Company Email'
            )
            ->addColumn(
                CompanyInterface::VAT_TAX_ID,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'VAT Tax ID'
            )
            ->addColumn(
                CompanyInterface::RESELLER_ID,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Reseller ID'
            )
            ->addColumn(
                CompanyInterface::COMMENT,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Comment'
            )
            ->addColumn(
                CompanyInterface::STREET,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Street'
            )
            ->addColumn(
                CompanyInterface::CITY,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'City'
            )
            ->addColumn(
                CompanyInterface::COUNTRY_ID,
                Table::TYPE_TEXT,
                2,
                ['nullable' => true],
                'Country ID'
            )
            ->addColumn(
                CompanyInterface::REGION,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Region'
            )
            ->addColumn(
                CompanyInterface::REGION_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true, 'identity' => false],
                'Region Id'
            )
            ->addColumn(
                CompanyInterface::POSTCODE,
                Table::TYPE_TEXT,
                30,
                ['nullable' => true],
                'Postcode'
            )
            ->addColumn(
                CompanyInterface::TELEPHONE,
                Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Telephone'
            )
            ->addColumn(
                CompanyInterface::CUSTOMER_GROUP_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true, 'identity' => false],
                'Customer Group ID'
            )
            ->addColumn(
                CompanyInterface::SALES_REPRESENTATIVE_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true, 'identity' => false],
                'Sales Representative ID'
            )
            ->addColumn(
                CompanyInterface::SUPER_USER_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true, 'identity' => false],
                'Super User ID'
            )
            ->addColumn(
                CompanyInterface::REJECT_REASON,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Reject Reason'
            )
            ->addColumn(
                CompanyInterface::REJECT_AT,
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Reject At'
            )
            ->addForeignKey(
                $installer->getFkName(
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::COUNTRY_ID,
                    'directory_country',
                    'country_id'
                ),
                CompanyInterface::COUNTRY_ID,
                $installer->getTable('directory_country'),
                'country_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName(
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::REGION_ID,
                    'directory_country_region',
                    'region_id'
                ),
                CompanyInterface::REGION_ID,
                $installer->getTable('directory_country_region'),
                'region_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName(
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::CUSTOMER_GROUP_ID,
                    'customer_group',
                    'customer_group_id'
                ),
                CompanyInterface::CUSTOMER_GROUP_ID,
                $installer->getTable('customer_group'),
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName(
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::SALES_REPRESENTATIVE_ID,
                    'admin_user',
                    'user_id'
                ),
                CompanyInterface::SALES_REPRESENTATIVE_ID,
                $installer->getTable('admin_user'),
                'user_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->addForeignKey(
                $installer->getFkName(
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::SUPER_USER_ID,
                    'customer_entity',
                    'entity_id'
                ),
                CompanyInterface::SUPER_USER_ID,
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Company Table');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createCustomerTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(CustomerInterface::TABLE_NAME))
            ->addColumn(
                CustomerInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Id'
            )
            ->addColumn(
                CustomerInterface::COMPANY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => true],
                'Company Id'
            )
            ->addColumn(
                CustomerInterface::JOB_TITLE,
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Job Title'
            )
            ->addColumn(
                CustomerInterface::TELEPHONE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Phone Number'
            )
            ->addColumn(
                CustomerInterface::ROLE_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => true],
                'Role Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable(CustomerInterface::TABLE_NAME),
                    [CustomerInterface::CUSTOMER_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CustomerInterface::CUSTOMER_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable(CustomerInterface::TABLE_NAME),
                    [CustomerInterface::ROLE_ID, CustomerInterface::COMPANY_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [CustomerInterface::ROLE_ID, CustomerInterface::COMPANY_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $installer->getFkName(
                    CustomerInterface::TABLE_NAME,
                    CustomerInterface::COMPANY_ID,
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::COMPANY_ID
                ),
                CustomerInterface::COMPANY_ID,
                $installer->getTable(CompanyInterface::TABLE_NAME),
                CompanyInterface::COMPANY_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    CustomerInterface::TABLE_NAME,
                    CustomerInterface::CUSTOMER_ID,
                    'customer_entity',
                    'entity_id'
                ),
                CustomerInterface::CUSTOMER_ID,
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    CustomerInterface::TABLE_NAME,
                    CustomerInterface::ROLE_ID,
                    RoleInterface::TABLE_NAME,
                    RoleInterface::ROLE_ID
                ),
                CustomerInterface::ROLE_ID,
                $installer->getTable(RoleInterface::TABLE_NAME),
                RoleInterface::ROLE_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Company Customer Table');

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createRoleTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(RoleInterface::TABLE_NAME))
            ->addColumn(
                RoleInterface::ROLE_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Role Id'
            )
            ->addColumn(
                RoleInterface::ROLE_NAME,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Role Name'
            )
            ->addColumn(
                RoleInterface::COMPANY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => false],
                'Company ID'
            )
            ->addColumn(
                RoleInterface::ROLE_TYPE_ID,
                Table::TYPE_INTEGER,
                1,
                ['identity' => false, 'unsigned' => true, 'nullable' => false, 'default' => 0],
                'Company ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable(RoleInterface::TABLE_NAME),
                    [RoleInterface::ROLE_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [RoleInterface::ROLE_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    RoleInterface::TABLE_NAME,
                    RoleInterface::COMPANY_ID,
                    CompanyInterface::TABLE_NAME,
                    CompanyInterface::COMPANY_ID
                ),
                RoleInterface::COMPANY_ID,
                $installer->getTable(CompanyInterface::TABLE_NAME),
                CompanyInterface::COMPANY_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Company Role Table');

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createPermissionTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(PermissionInterface::TABLE_NAME))
            ->addColumn(
                PermissionInterface::PERMISSION_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => false, 'nullable' => false, 'primary' => true],
                'Permission ID'
            )
            ->addColumn(
                PermissionInterface::ROLE_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => false],
                'Role ID'
            )
            ->addColumn(
                PermissionInterface::RESOURCE_ID,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Resource ID'
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable(PermissionInterface::TABLE_NAME),
                    [PermissionInterface::PERMISSION_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [PermissionInterface::PERMISSION_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable(PermissionInterface::TABLE_NAME),
                    [PermissionInterface::ROLE_ID],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                [PermissionInterface::ROLE_ID],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $installer->getFkName(
                    PermissionInterface::TABLE_NAME,
                    PermissionInterface::ROLE_ID,
                    RoleInterface::TABLE_NAME,
                    RoleInterface::ROLE_ID
                ),
                PermissionInterface::ROLE_ID,
                $installer->getTable(RoleInterface::TABLE_NAME),
                RoleInterface::ROLE_ID,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Company Permissions Table');

        $installer->getConnection()->createTable($table);
    }
}
