<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\Constants;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\GetValue as GetCommentValue;
use Amasty\CompanyAccount\Model\WebsiteCurrency;

class UpdateRates
{
    /**
     * @var WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var GetCommentValue
     */
    private $getCommentValue;

    public function __construct(
        WebsiteCurrency $websiteCurrency,
        GetCommentValue $getCommentValue
    ) {
        $this->websiteCurrency = $websiteCurrency;
        $this->getCommentValue = $getCommentValue;
    }

    public function execute(CreditEventInterface $creditEvent): void
    {
        if ($creditEvent->getCurrencyEvent() != $creditEvent->getCurrencyCredit()) {
            $creditRate = $this->websiteCurrency->getBaseRate(
                $creditEvent->getCurrencyEvent(),
                $creditEvent->getCurrencyCredit()
            );
            $creditEvent->setCreditRate($creditRate);
        }

        $displayCurrency = $this->getCommentValue->execute($creditEvent, Constants::DISPLAY_CURRENCY);
        if ($displayCurrency && $displayCurrency != $creditEvent->getCurrencyEvent()) {
            $rate = $this->websiteCurrency->getBaseRate($creditEvent->getCurrencyEvent(), $displayCurrency);
            $creditEvent->setRate($rate);
            $creditEvent->setCurrencyEvent($displayCurrency);
        }
    }
}
