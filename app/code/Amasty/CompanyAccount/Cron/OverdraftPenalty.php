<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Cron;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Credit\AppendCreditEvent;
use Amasty\CompanyAccount\Model\Credit\Event\Query\CreateCreditEventInterface;
use Amasty\CompanyAccount\Model\Credit\Event\Query\GetEventsCountInterface;
use Amasty\CompanyAccount\Model\Credit\Query\GetByIdInterface as GetCreditById;
use Amasty\CompanyAccount\Model\MailManager;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft\GetCreditIdsForPenalty;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft\IsPenaltyAppliedForCredit;
use Amasty\CompanyAccount\Model\Source\Credit\Operation;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class OverdraftPenalty
{
    /**
     * @var GetCreditIdsForPenalty
     */
    private $getCreditIdsForPenalty;

    /**
     * @var IsPenaltyAppliedForCredit
     */
    private $isPenaltyAppliedForCredit;

    /**
     * @var GetCreditById
     */
    private $getCreditById;

    /**
     * @var CreateCreditEventInterface
     */
    private $createCreditEvent;

    /**
     * @var AppendCreditEvent
     */
    private $appendCreditEvent;

    /**
     * @var GetEventsCountInterface
     */
    private $getEventsCount;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetCreditIdsForPenalty $getCreditIdsForPenalty,
        IsPenaltyAppliedForCredit $isPenaltyAppliedForCredit,
        GetCreditById $getCreditById,
        CreateCreditEventInterface $createCreditEvent,
        AppendCreditEvent $appendCreditEvent,
        GetEventsCountInterface $getEventsCount,
        MailManager $mailManager,
        LoggerInterface $logger
    ) {
        $this->getCreditIdsForPenalty = $getCreditIdsForPenalty;
        $this->isPenaltyAppliedForCredit = $isPenaltyAppliedForCredit;
        $this->getCreditById = $getCreditById;
        $this->createCreditEvent = $createCreditEvent;
        $this->appendCreditEvent = $appendCreditEvent;
        $this->getEventsCount = $getEventsCount;
        $this->mailManager = $mailManager;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $creditIds = $this->getCreditIdsForPenalty->execute();

        foreach ($creditIds as $creditId) {
            $creditId = (int) $creditId;

            try {
                $credit = $this->getCreditById->execute($creditId);
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
                continue;
            }

            if ($credit->getBalance() >= 0 || $this->isPenaltyAppliedForCredit->execute($creditId)) {
                continue;
            }

            $overdraftValue = abs($credit->getBalance());
            $penalty = $overdraftValue * $credit->getOverdraftPenalty() / 100;

            $creditEvent = $this->createCreditEvent->execute([
                CreditEventInterface::AMOUNT => -1 * $penalty,
                CreditEventInterface::TYPE => Operation::OVERDRAFT_PENALTY,
                CreditEventInterface::CURRENCY_EVENT => $credit->getCurrencyCode(),
                CreditEventInterface::CURRENCY_CREDIT => $credit->getCurrencyCode()
            ]);

            try {
                $this->appendCreditEvent->execute($credit, [$creditEvent]);
                // if first penalty applied send email
                if ($this->getEventsCount->execute((int) $credit->getId(), Operation::OVERDRAFT_PENALTY) === 1) {
                    $this->mailManager->sendOverdraftPenalty($credit);
                }
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
