<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Credit;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit as CreditModel;
use Amasty\CompanyAccount\Model\ResourceModel\Credit as CreditResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = CreditInterface::ID;

    protected function _construct()
    {
        $this->_init(CreditModel::class, CreditResource::class);
    }
}
