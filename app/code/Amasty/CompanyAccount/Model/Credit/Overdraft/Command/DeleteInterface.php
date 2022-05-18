<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Command;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Magento\Framework\Exception\CouldNotDeleteException;

interface DeleteInterface
{
    /**
     * @param OverdraftInterface $overdraft
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(OverdraftInterface $overdraft): void;
}
