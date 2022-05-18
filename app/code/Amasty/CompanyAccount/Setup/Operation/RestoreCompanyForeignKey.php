<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class RestoreCompanyForeignKey
{
    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $connection = $setup->getConnection();
        $mainTable = $setup->getTable(CustomerInterface::TABLE_NAME);

        $connection->addForeignKey(
            $setup->getFkName(
                CustomerInterface::TABLE_NAME,
                CustomerInterface::COMPANY_ID,
                CompanyInterface::TABLE_NAME,
                CompanyInterface::COMPANY_ID
            ),
            $mainTable,
            CustomerInterface::COMPANY_ID,
            $setup->getTable(CompanyInterface::TABLE_NAME),
            CompanyInterface::COMPANY_ID
        );
    }
}
