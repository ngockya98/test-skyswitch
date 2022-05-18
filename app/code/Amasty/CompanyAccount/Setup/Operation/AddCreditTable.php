<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class AddCreditTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection('sales')->newTable(
            $setup->getTable(CreditInterface::MAIN_TABLE)
        )->addColumn(
            CreditInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id.'
        )->addColumn(
            CreditInterface::COMPANY_ID,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Company Id.'
        )->addColumn(
            CreditInterface::BALANCE,
            Table::TYPE_DECIMAL,
            '20,4',
            ['nullable' => false, 'unsigned' => false],
            'Balance.'
        )->addColumn(
            CreditInterface::ISSUED_CREDIT,
            Table::TYPE_DECIMAL,
            '20,4',
            ['nullable' => false, 'unsigned' => false],
            'Issued credit.'
        )->addColumn(
            CreditInterface::BE_PAID,
            Table::TYPE_DECIMAL,
            '20,4',
            ['nullable' => false, 'unsigned' => false],
            'To be paid.'
        )->addColumn(
            CreditInterface::CURRENCY_CODE,
            Table::TYPE_TEXT,
            3,
            ['nullable' => false],
            'Currency of credit.'
        )->addColumn(
            CreditInterface::ALLOW_OVERDRAFT,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0, 'nullable' => false],
            'Is overdraft allowed.'
        )->addColumn(
            CreditInterface::OVERDRAFT_LIMIT,
            Table::TYPE_DECIMAL,
            '20,4',
            ['nullable' => true, 'unsigned' => false],
            'Overdraft limit.'
        )->addColumn(
            CreditInterface::OVERDRAFT_REPAY_PERIOD,
            Table::TYPE_SMALLINT,
            null,
            ['default' => 0, 'nullable' => false],
            'Overdraft repay period.'
        )->addColumn(
            CreditInterface::OVERDRAFT_REPAY_DIGIT,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Number of days/months/years.'
        )->addColumn(
            CreditInterface::OVERDRAFT_REPAY_TYPE,
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Days/months/years.'
        )->addColumn(
            CreditInterface::OVERDRAFT_PENALTY,
            Table::TYPE_FLOAT,
            null,
            ['nullable' => true],
            'Penalty if repay period expired.'
        )->addIndex(
            $setup->getIdxName(
                CreditInterface::MAIN_TABLE,
                [CreditInterface::COMPANY_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [CreditInterface::COMPANY_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $setup->getFkName(
                CreditInterface::MAIN_TABLE,
                CreditInterface::COMPANY_ID,
                CompanyInterface::TABLE_NAME,
                CompanyInterface::COMPANY_ID
            ),
            CreditInterface::COMPANY_ID,
            $setup->getTable(CompanyInterface::TABLE_NAME),
            CompanyInterface::COMPANY_ID,
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
