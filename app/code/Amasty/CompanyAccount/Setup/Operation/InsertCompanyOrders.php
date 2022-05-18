<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup\Operation;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\Data\OrderInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InsertCompanyOrders
{
    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select = $connection->select();
        $orderTable = $setup->getTable('sales_order');

        $select->from($orderTable, [OrderInterface::COMPANY_ORDER_ID => 'entity_id'])
            ->joinInner(
                ['customer' => $setup->getTable(CustomerInterface::TABLE_NAME)],
                $orderTable . '.customer_id = customer.customer_id',
                [CustomerInterface::COMPANY_ID]
            )
            ->joinInner(
                ['company' => $setup->getTable(CompanyInterface::TABLE_NAME)],
                'customer.company_id = company.company_id',
                [CompanyInterface::COMPANY_NAME]
            );

        if ($data = $connection->fetchAll($select)) {
            $connection->insertOnDuplicate(
                $setup->getTable(OrderInterface::TABLE_NAME),
                $data
            );
        }
    }
}
