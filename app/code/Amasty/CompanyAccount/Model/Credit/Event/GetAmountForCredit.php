<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Price\Convert as ConvertPrice;

class GetAmountForCredit
{
    /**
     * @var ConvertPrice
     */
    private $convertPrice;

    public function __construct(ConvertPrice $convertPrice)
    {
        $this->convertPrice = $convertPrice;
    }

    /**
     * Calculate operation amount in Credit Entity Currency.
     *
     * @param CreditEventInterface $creditEvent
     * @return float
     */
    public function execute(CreditEventInterface $creditEvent): float
    {
        return $this->convertPrice->execute(
            $creditEvent->getAmount(),
            $creditEvent->getCurrencyEvent(),
            $creditEvent->getCurrencyCredit()
        );
    }
}
