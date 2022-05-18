<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CreditEvent extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(CreditEventInterface::MAIN_TABLE, CreditEventInterface::ID);
    }
}
