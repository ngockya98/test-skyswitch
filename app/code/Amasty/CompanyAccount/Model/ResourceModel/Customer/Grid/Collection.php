<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Customer\Grid;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerGridCollection;

class Collection extends CustomerGridCollection
{
    public const CUSTOMER_ENTITY_TABLE = 'customer_entity';

    /**
     * @var array
     */
    private $mappedFields = [
        'email' => 'main_table.email',
        'group_id' => 'main_table.group_id'
    ];

    /**
     * @return $this
     */
    public function addCompanyDataToSelect()
    {
        $companyTable = $this->getResource()->getTable(CompanyInterface::TABLE_NAME);
        $customerTable = $this->getResource()->getTable(CustomerInterface::TABLE_NAME);
        $roleTable = $this->getResource()->getTable(RoleInterface::TABLE_NAME);

        $this->getSelect()->joinLeft(
            ['company_customer' => $customerTable],
            'main_table.entity_id = company_customer.customer_id'
        )->joinLeft(
            ['company_role' => $roleTable],
            'company_customer.role_id = company_role.role_id',
            ['role' => 'company_role.role_name']
        )->joinLeft(
            ['company' => $companyTable],
            'company.company_id = company_customer.company_id',
            ['company_name']
        );

        return $this;
    }

    /**
     * @param array $customerIds
     * @param bool $exclude
     * @return $this
     */
    public function addCustomerIdFilter($customerIds = [], $exclude = false)
    {
        if (!is_array($customerIds)) {
            $customerIds = [$customerIds];
        }

        if ($exclude) {
            if (!empty($customerIds)) {
                $this->getSelect()->where('main_table.entity_id not in (?)', $customerIds);
            }
        } else {
            $this->getSelect()->where('main_table.entity_id in (?)', $customerIds);
        }

        return $this;
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
