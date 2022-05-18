<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

class RepayDateRetrieveStrategy implements RetrieveStrategyInterface
{
    public function execute(string $value): string
    {
        return __('To be paid until %1', $value)->render();
    }
}
