<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Role;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\CompanyAccount\Model\Role::class,
            \Amasty\CompanyAccount\Model\ResourceModel\Role::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
