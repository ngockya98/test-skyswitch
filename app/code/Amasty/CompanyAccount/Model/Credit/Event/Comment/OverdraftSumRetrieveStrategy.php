<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

class OverdraftSumRetrieveStrategy implements RetrieveStrategyInterface
{
    public function execute(string $value): string
    {
        return __('Overdraft used: %1', $value)->render();
    }
}
