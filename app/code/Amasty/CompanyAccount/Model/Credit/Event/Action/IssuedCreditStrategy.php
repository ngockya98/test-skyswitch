<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Action;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\GetAmountForCredit;
use Amasty\CompanyAccount\Model\MailManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class IssuedCreditStrategy implements ChangeCreditStrategyInterface
{
    /**
     * @var GetAmountForCredit
     */
    private $getAmountForCredit;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetAmountForCredit $getAmountForCredit,
        MailManager $mailManager,
        LoggerInterface $logger
    ) {
        $this->getAmountForCredit = $getAmountForCredit;
        $this->mailManager = $mailManager;
        $this->logger = $logger;
    }

    public function execute(CreditInterface $credit, CreditEventInterface $creditEvent): void
    {
        $amount = $this->getAmountForCredit->execute($creditEvent);

        $credit->setIssuedCredit($credit->getIssuedCredit() + $amount);

        $newBalance = $credit->getBalance() + $amount;
        $creditEvent->setBalance($newBalance);
        $credit->setBalance($newBalance);

        try {
            $this->mailManager->sendCreditChangesByAdmin($credit->getCompanyId(), $creditEvent);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
