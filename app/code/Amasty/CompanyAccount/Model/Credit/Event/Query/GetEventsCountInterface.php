<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Query;

/**
 * @api
 */
interface GetEventsCountInterface
{
    public function execute(int $creditId, ?string $eventType = null): int;
}
