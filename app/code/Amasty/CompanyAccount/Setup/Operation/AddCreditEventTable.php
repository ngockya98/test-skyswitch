<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class AddCreditEventTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection('sales')->newTable(
            $setup->getTable(CreditEventInterface::MAIN_TABLE)
        )->addColumn(
            CreditEventInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id.'
        )->addColumn(
            CreditEventInterface::CREDIT_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Credit Id.'
        )->addColumn(
            CreditEventInterface::USER_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'User Id.'
        )->addColumn(
            CreditEventInterface::USER_TYPE,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true],
            'User Type.'
        )->addColumn(
            CreditEventInterface::TYPE,
            Table::TYPE_TEXT,
            25,
            ['nullable' => false],
            'Type of operation.'
        )->addColumn(
            CreditEventInterface::AMOUNT,
            Table::TYPE_DECIMAL,
            '20,4',
            ['nullable' => false, 'unsigned' => false],
            'Amount of operation(saved in base currency of operation (order, etc.)).'
        )->addColumn(
            CreditEventInterface::CURRENCY_CREDIT,
            Table::TYPE_TEXT,
            3,
            ['nullable' => false],
            'Currency of credit.'
        )->addColumn(
            CreditEventInterface::CURRENCY_EVENT,
            Table::TYPE_TEXT,
            3,
            ['nullable' => false],
            'Currency of operation.'
        )->addColumn(
            CreditEventInterface::RATE,
            Table::TYPE_DECIMAL,
            '24,12',
            ['nullable' => false, 'unsigned' => false, 'default' => 0],
            'Rate for convert in Operation Currency.'
        )->addColumn(
            CreditEventInterface::RATE_CREDIT,
            Table::TYPE_DECIMAL,
            '24,12',
            ['nullable' => false, 'unsigned' => false, 'default' => 0],
            'Rate for convert in Credit Currency.'
        )->addColumn(
            CreditEventInterface::BALANCE,
            Table::TYPE_DECIMAL,
            '20,4',
            ['nullable' => false, 'unsigned' => false],
            'Balance at moment (Saved in Credit Currency).'
        )->addColumn(
            CreditEventInterface::DATE,
            Table::TYPE_TIMESTAMP,
            null,
            ['default' => Table::TIMESTAMP_INIT],
            'Timestamp of Operation.'
        )->addColumn(
            CreditEventInterface::COMMENT,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Comment of Operation.'
        )->addIndex(
            $setup->getIdxName(
                CreditEventInterface::MAIN_TABLE,
                [CreditEventInterface::CREDIT_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [CreditEventInterface::CREDIT_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $setup->getFkName(
                CreditEventInterface::MAIN_TABLE,
                CreditEventInterface::CREDIT_ID,
                CreditInterface::MAIN_TABLE,
                CreditInterface::ID
            ),
            CreditEventInterface::CREDIT_ID,
            $setup->getTable(CreditInterface::MAIN_TABLE),
            CreditInterface::ID,
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
