<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Order;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Credit\AppendCreditEvent;
use Amasty\CompanyAccount\Model\Credit\Event\Comment\Constants as CommentConstants;
use Amasty\CompanyAccount\Model\Credit\Event\Query\CreateCreditEventInterface;
use Amasty\CompanyAccount\Model\Source\Credit\Operation;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * @api
 */
class Cancel
{
    /**
     * @var GetCreditByOrder
     */
    private $getCreditByOrder;

    /**
     * @var CreateCreditEventInterface
     */
    private $createCreditEvent;

    /**
     * @var AppendCreditEvent
     */
    private $appendCreditEvent;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        GetCreditByOrder $getCreditByOrder,
        CreateCreditEventInterface $createCreditEvent,
        AppendCreditEvent $appendCreditEvent,
        LoggerInterface $logger
    ) {
        $this->getCreditByOrder = $getCreditByOrder;
        $this->createCreditEvent = $createCreditEvent;
        $this->appendCreditEvent = $appendCreditEvent;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(OrderInterface $order): void
    {
        $credit = $this->getCreditByOrder->execute($order);

        $creditEvent = $this->createCreditEvent->execute([
            CreditEventInterface::AMOUNT => $order->getBaseGrandTotal(),
            CreditEventInterface::TYPE => Operation::CANCEL_ORDER,
            CreditEventInterface::CURRENCY_EVENT => $order->getBaseCurrencyCode(),
            CreditEventInterface::CURRENCY_CREDIT => $credit->getCurrencyCode(),
            CreditEventInterface::COMMENT => [
                CommentConstants::ORDER_INCREMENT => $order->getIncrementId(),
                CommentConstants::DISPLAY_CURRENCY => $order->getOrderCurrencyCode(),
                CommentConstants::BASE_CURRENCY => $order->getBaseCurrencyCode()
            ]
        ]);

        try {
            $this->appendCreditEvent->execute($credit, [$creditEvent]);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            throw new LocalizedException(__('Can\'t cancel order with Company Store Credit'));
        }
    }
}
