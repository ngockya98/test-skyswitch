<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

interface RetrieveStrategyInterface
{
    public function execute(string $value): string;
}
