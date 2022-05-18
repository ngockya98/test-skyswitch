<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Condition;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Validation\ValidationException;

interface ConditionInterface
{
    /**
     * @param CreditInterface $credit
     * @param CreditEventInterface $creditEvent
     * @return void
     * @throws ValidationException
     */
    public function validate(CreditInterface $credit, CreditEventInterface $creditEvent): void;
}
