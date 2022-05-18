<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

class DefaultRetrieveStrategy implements RetrieveStrategyInterface
{
    public function execute(string $value): string
    {
        return $value;
    }
}
