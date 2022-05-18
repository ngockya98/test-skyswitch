<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\OrderInterface;
use Magento\Framework\DB\Ddl\Table;

class AddCompanyOrders
{
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $table  = $setup->getConnection('sales')
            ->newTable($setup->getTable(OrderInterface::TABLE_NAME))
            ->addColumn(
                OrderInterface::COMPANY_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                OrderInterface::COMPANY_ID,
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Company Id'
            )
            ->addColumn(
                OrderInterface::COMPANY_NAME,
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => true],
                'Company Name'
            )
            ->addForeignKey(
                $setup->getFkName(
                    $setup->getTable(OrderInterface::TABLE_NAME),
                    OrderInterface::COMPANY_ORDER_ID,
                    $setup->getTable('sales_order'),
                    'entity_id'
                ),
                OrderInterface::COMPANY_ORDER_ID,
                $setup->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($table);
    }
}
