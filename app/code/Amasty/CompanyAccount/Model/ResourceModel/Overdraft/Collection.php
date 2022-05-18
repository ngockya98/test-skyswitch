<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Overdraft;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Model\Overdraft as OverdraftModel;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft as OverdraftResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = OverdraftInterface::ID;

    protected function _construct()
    {
        $this->_init(OverdraftModel::class, OverdraftResource::class);
    }
}
