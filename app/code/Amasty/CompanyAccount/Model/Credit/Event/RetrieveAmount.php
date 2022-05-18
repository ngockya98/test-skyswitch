<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;

/**
 * Retrieve amount in display currency.
 */
class RetrieveAmount
{
    public function execute(CreditEventInterface $creditEvent): float
    {
        $amount = $creditEvent->getAmount();
        if ($creditEvent->getRate()) {
            $amount *= $creditEvent->getRate();
        }

        return $amount;
    }
}
