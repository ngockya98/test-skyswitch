<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Model\ResourceModel\Overdraft\IsOverdraftExceed;

class IsExceed implements IsExceedInterface
{
    /**
     * @var IsOverdraftExceed
     */
    private $isOverdraftExceed;

    public function __construct(IsOverdraftExceed $isOverdraftExceed)
    {
        $this->isOverdraftExceed = $isOverdraftExceed;
    }

    public function execute(int $creditId): bool
    {
        return $this->isOverdraftExceed->execute($creditId);
    }
}
