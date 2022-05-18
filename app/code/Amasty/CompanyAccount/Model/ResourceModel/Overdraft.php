<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Overdraft extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(OverdraftInterface::MAIN_TABLE, OverdraftInterface::ID);
    }
}
