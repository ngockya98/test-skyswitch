<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;

class DeleteForeignKey
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $mainTable = $setup->getTable(CustomerInterface::TABLE_NAME);

        $connection->dropForeignKey(
            $mainTable,
            $setup->getFkName(
                CustomerInterface::TABLE_NAME,
                CustomerInterface::COMPANY_ID,
                CompanyInterface::TABLE_NAME,
                CompanyInterface::COMPANY_ID
            )
        );
    }
}
