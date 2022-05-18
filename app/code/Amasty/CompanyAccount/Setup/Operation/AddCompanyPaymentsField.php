<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;

class AddCompanyPaymentsField
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable(CompanyInterface::TABLE_NAME);

        $connection->addColumn(
            $tableName,
            CompanyInterface::RESTRICTED_PAYMENTS,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'identity' => false,
                'primary' => false,
                'comment' => 'Restricted Payment Method Codes'
            ]
        );
    }
}
