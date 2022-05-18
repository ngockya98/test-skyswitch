<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class AddOverdraftTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection('sales')->newTable(
            $setup->getTable(OverdraftInterface::MAIN_TABLE)
        )->addColumn(
            OverdraftInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id.'
        )->addColumn(
            OverdraftInterface::CREDIT_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Credit Id.'
        )->addColumn(
            OverdraftInterface::START_DATE,
            Table::TYPE_TIMESTAMP,
            null,
            ['default' => Table::TIMESTAMP_INIT, 'nullable' => false],
            'Timestamp when Overdraft created.'
        )->addColumn(
            OverdraftInterface::REPAY_DATE,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Timestamp when Overdraft calculate penalty if not repayed.'
        )->addIndex(
            $setup->getIdxName(
                OverdraftInterface::MAIN_TABLE,
                [OverdraftInterface::REPAY_DATE],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [OverdraftInterface::REPAY_DATE],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addIndex(
            $setup->getIdxName(
                OverdraftInterface::MAIN_TABLE,
                [OverdraftInterface::CREDIT_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [OverdraftInterface::CREDIT_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $setup->getFkName(
                OverdraftInterface::MAIN_TABLE,
                OverdraftInterface::CREDIT_ID,
                CreditInterface::MAIN_TABLE,
                CreditInterface::ID
            ),
            OverdraftInterface::CREDIT_ID,
            $setup->getTable(CreditInterface::MAIN_TABLE),
            CreditInterface::ID,
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
