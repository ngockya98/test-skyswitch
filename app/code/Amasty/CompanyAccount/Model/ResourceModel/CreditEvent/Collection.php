<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\CreditEvent;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\CreditEvent as CreditEventModel;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent as CreditEventResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = CreditEventInterface::ID;

    protected function _construct()
    {
        $this->_init(CreditEventModel::class, CreditEventResource::class);
    }
}
