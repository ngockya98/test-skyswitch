<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;

class AddUserStatusField
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable(CustomerInterface::TABLE_NAME);

        $connection->addColumn(
            $tableName,
            CustomerInterface::STATUS,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'size' => 1,
                'nullable' => false,
                'default' => 1,
                'identity' => false,
                'primary' => false,
                'comment' => 'User Status'
            ]
        );
    }
}
