<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Action;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\GetAmountForCredit;

class DefaultStrategy implements ChangeCreditStrategyInterface
{
    /**
     * @var GetAmountForCredit
     */
    private $getAmountForCredit;

    public function __construct(GetAmountForCredit $getAmountForCredit)
    {
        $this->getAmountForCredit = $getAmountForCredit;
    }

    public function execute(CreditInterface $credit, CreditEventInterface $creditEvent): void
    {
        $amount = $this->getAmountForCredit->execute($creditEvent);

        $newBalance = $credit->getBalance() + $amount;
        $creditEvent->setBalance($newBalance);
        $credit->setBalance($newBalance);
    }
}
