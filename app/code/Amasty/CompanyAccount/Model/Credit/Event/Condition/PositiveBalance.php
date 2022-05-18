<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Condition;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Condition\ConditionInterface;
use Amasty\CompanyAccount\Model\Credit\Event\GetAmountForCredit;
use Magento\Framework\Phrase;
use Magento\Framework\Validation\ValidationException;

class PositiveBalance implements ConditionInterface
{
    /**
     * @var GetAmountForCredit
     */
    private $getAmountForCredit;

    public function __construct(GetAmountForCredit $getAmountForCredit)
    {
        $this->getAmountForCredit = $getAmountForCredit;
    }

    /**
     * @param CreditInterface $credit
     * @param CreditEventInterface $creditEvent
     * @return void
     * @throws ValidationException
     */
    public function validate(CreditInterface $credit, CreditEventInterface $creditEvent): void
    {
        $availableAmount = $credit->getBalance();
        $eventAmountInCreditCurrency = $this->getAmountForCredit->execute($creditEvent);

        if ($credit->isOverdraftAllowed()) {
            $availableAmount += $credit->getOverdraftLimit();
        }

        if ($availableAmount < $eventAmountInCreditCurrency) {
            throw new ValidationException(
                __('The operation can’t be performed because it exceeds the available credit amount.')
            );
        }
    }
}
