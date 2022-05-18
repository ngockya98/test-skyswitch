<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\OrderInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OrderInterface::TABLE_NAME, OrderInterface::COMPANY_ORDER_ID);
    }

    /**
     * @param array $data
     * @return array
     */
    public function saveData(array $data = []): array
    {
        $this->getConnection()->insertOnDuplicate($this->getTable(OrderInterface::TABLE_NAME), $data);

        return $data;
    }

    /**
     * @param int $orderId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderExtensionAttributes($orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where(OrderInterface::COMPANY_ORDER_ID . ' = ?', $orderId)
            ->limit(1);

        return $connection->fetchRow($select);
    }

    /**
     * @param SearchResultInterface $collection
     * @return SearchResultInterface
     */
    public function addCompanyOrderToSelect(SearchResultInterface $collection, $orderField)
    {
        $collection->getSelect()
            ->joinLeft(
                ['company_order' => $this->getMainTable()],
                'company_order.company_order_id = ' . $orderField,
                ['company_order.company_name']
            );

        return $collection;
    }

    /**
     * @param int $companyId
     * @return Select
     */
    public function getCompanyOrders(int $companyId): Select
    {
        return $this->getConnection()->select()
            ->from($this->getMainTable(), [OrderInterface::COMPANY_ORDER_ID])
            ->where(
                sprintf('%s = ?', OrderInterface::COMPANY_ID),
                $companyId
            );
    }

    /**
     * @param int $orderId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyIdByOrder(int $orderId): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [OrderInterface::COMPANY_ID])
            ->where(
                sprintf('%s = ?', OrderInterface::COMPANY_ORDER_ID),
                $orderId
            );

        return (int) ($this->getConnection()->fetchRow($select)[OrderInterface::COMPANY_ID] ?? 0);
    }

    /**
     * @param int $orderId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyNameByOrderId(int $orderId): string
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [OrderInterface::COMPANY_NAME])
            ->where(
                sprintf('%s = ?', OrderInterface::COMPANY_ORDER_ID),
                $orderId
            );

        return $this->getConnection()->fetchRow($select)[OrderInterface::COMPANY_NAME] ?? '';
    }
}
