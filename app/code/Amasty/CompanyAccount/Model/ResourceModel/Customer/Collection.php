<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Customer;

use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\CompanyAccount\Model\Customer::class,
            \Amasty\CompanyAccount\Model\ResourceModel\Customer::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param int $companyId
     * @return $this
     */
    public function getCompanyCustomers(int $companyId)
    {
        $select = $this->getSelect();
        $select->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            ['entity_id', 'email']
        )->joinLeft(
            ['roles' => $this->getTable(RoleInterface::TABLE_NAME)],
            'roles.role_id = main_table.role_id',
            ['role_name']
        )
            ->where('main_table.company_id = ?', $companyId)
            ->order('entity_id ASC');

        return $this;
    }
}
