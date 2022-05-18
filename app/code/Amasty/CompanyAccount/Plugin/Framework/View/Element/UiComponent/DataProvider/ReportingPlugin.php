<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class ReportingPlugin
{
    public const MAIN_ORDER_TABLE = 'sales_order_grid';
    public const CUSTOMER_TABLE = 'customer_grid_flat';

    public const SALES_TABLE_LIST = [
        'sales_order_grid',
        'sales_invoice_grid',
        'sales_shipment_grid',
        'sales_creditmemo_grid'
    ];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $connection;

    /**
     * @var array
     */
    private $salesTables = [];

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Order
     */
    private $order;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $connection,
        \Amasty\CompanyAccount\Model\ResourceModel\Order $order
    ) {
        $this->connection = $connection;
        $this->initTables();
        $this->order = $order;
    }

    /**
     * @param Reporting $subject
     * @param SearchResultInterface $collection
     * @return mixed
     */
    public function afterSearch(Reporting $subject, SearchResultInterface $collection)
    {
        $mainTable = $collection->getMainTable();
        if (in_array($mainTable, $this->salesTables)) {
            $collection = $this->processSalesGrid($mainTable, $collection);
        } elseif ($mainTable == $this->connection->getTableName(self::CUSTOMER_TABLE)) {
            $collection = $this->addCompanyNameToSelect($collection);
        }

        return $collection;
    }

    /**
     * @param string $mainTable
     * @param SearchResultInterface $collection
     * @return SearchResultInterface
     */
    private function processSalesGrid(string $mainTable, SearchResultInterface $collection)
    {
        $orderField = $this->getOrderIdField($mainTable);
        $this->order->addCompanyOrderToSelect($collection, $orderField);

        return $collection;
    }

    /**
     * @param string $mainTable
     * @return string
     */
    private function getOrderIdField(string $mainTable)
    {
        $salesOrderTable = $this->salesTables[self::MAIN_ORDER_TABLE];

        return $mainTable == $salesOrderTable ? 'main_table.entity_id' : 'main_table.order_id';
    }

    /**
     * @param SearchResultInterface $collection
     * @return SearchResultInterface
     */
    private function addCompanyNameToSelect(SearchResultInterface $collection)
    {
        $customerTable = $this->connection->getTableName(CustomerInterface::TABLE_NAME);
        $companyTable = $this->connection->getTableName(CompanyInterface::TABLE_NAME);

        $collection
            ->getSelect()
            ->joinLeft(
                ['customer' => $customerTable],
                'customer.customer_id = ' . 'main_table.entity_id',
                []
            )->joinLeft(
                ['company' => $companyTable],
                'company.company_id = customer.company_id',
                [
                    'company_name' => 'company.company_name'
                ]
            );

        return $collection;
    }

    private function initTables()
    {
        foreach (self::SALES_TABLE_LIST as $mainTable) {
            $this->salesTables[$mainTable] = $this->connection->getTableName($mainTable);
        }
    }
}
