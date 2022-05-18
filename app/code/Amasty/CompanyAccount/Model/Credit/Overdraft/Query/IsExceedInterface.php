<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

/**
 * @api
 */
interface IsExceedInterface
{
    public function execute(int $creditId): bool;
}
