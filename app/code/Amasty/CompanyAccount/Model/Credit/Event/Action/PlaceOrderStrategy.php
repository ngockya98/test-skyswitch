<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Action;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Event\AddComments;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\Constants as CommentConstants;
use Amasty\CompanyAccount\Model\Credit\Overdraft\CalculateRepayDate;
use Amasty\CompanyAccount\Model\MailManager;
use Amasty\CompanyAccount\Model\Source\Credit\Operation;
use Amasty\CompanyAccount\Model\Source\Credit\OverdraftRepay;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class PlaceOrderStrategy implements ChangeCreditStrategyInterface
{
    /**
     * @var AddComments
     */
    private $addComments;

    /**
     * @var CalculateRepayDate
     */
    private $calculateRepayDate;

    /**
     * @var BePaidStrategy
     */
    private $bePaidStrategy;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        BePaidStrategy $bePaidStrategy,
        AddComments $addComments,
        CalculateRepayDate $calculateRepayDate,
        MailManager $mailManager,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger
    ) {
        $this->addComments = $addComments;
        $this->calculateRepayDate = $calculateRepayDate;
        $this->bePaidStrategy = $bePaidStrategy;
        $this->mailManager = $mailManager;
        $this->logger = $logger;
        $this->priceCurrency = $priceCurrency;
    }

    public function execute(CreditInterface $credit, CreditEventInterface $creditEvent): void
    {
        $this->bePaidStrategy->execute($credit, $creditEvent);

        if ($credit->getBalance() < 0) {
            $creditEvent->setType(Operation::PLACE_ORDER_OVERDRAFT);

            $comments = [
                CommentConstants::OVERDRAFT_SUM => $this->formatPrice(
                    $credit->getBalance(),
                    $creditEvent->getCurrencyCredit()
                )
            ];
            if ($credit->getOverdraftRepay() === OverdraftRepay::SET) {
                $comments[CommentConstants::REPAY_DATE] = $this->calculateRepayDate->execute($credit);
            }
            $this->addComments->execute($creditEvent, $comments);

            if ($credit->getOverdraftRepay() === OverdraftRepay::SET) {
                try {
                    $this->mailManager->sendOverdraftUsed($credit);
                } catch (LocalizedException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }

    private function formatPrice(float $amount, ?string $currency): string
    {
        return $this->priceCurrency->format(
            $amount,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $currency
        );
    }
}
