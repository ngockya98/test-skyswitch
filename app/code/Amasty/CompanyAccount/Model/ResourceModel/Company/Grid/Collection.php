<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Company\Grid;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Company;
use Amasty\CompanyAccount\Model\Source\Company\Group;
use Amasty\CompanyAccount\Model\Source\Company\GroupGrid;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @var string
     */
    protected $document = Company::class;

    /**
     * @var array
     */
    private $mappedFields = [];

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = CompanyInterface::TABLE_NAME,
        $resourceModel = \Amasty\CompanyAccount\Model\ResourceModel\Company::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->mappedFields = [
            'company_admin' => new \Zend_Db_Expr('CONCAT(customer.firstname, " ", customer.lastname)')
        ];
    }

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinInner(
            ['customer' => $this->getResource()->getTable('customer_entity')],
            'customer.entity_id = main_table.super_user_id',
            $this->mappedFields
        );

        $this->getSelect()->columns([CompanyInterface::CUSTOMER_GROUP_ID => new \Zend_Db_Expr(
            sprintf(
                'IF(main_table.use_company_group = 0, %s, main_table.customer_group_id)',
                GroupGrid::DEFAULT_GROUP_ID
            )
        )]);

        parent::_renderFiltersBefore();
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }
        return parent::addOrder($field, $direction);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return Collection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        return parent::setOrder($field, $direction);
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
