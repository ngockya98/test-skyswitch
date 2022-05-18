<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;

class AddUseCompanyGroupField
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
            CompanyInterface::USE_COMPANY_GROUP,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'size' => 1,
                'nullable' => false,
                'default' => 1,
                'identity' => false,
                'primary' => false,
                'comment' => 'Use Company Customer Group'
            ]
        );
    }
}
