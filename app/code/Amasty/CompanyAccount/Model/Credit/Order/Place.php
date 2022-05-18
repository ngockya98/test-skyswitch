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
class Place
{
    /**
     * @var GetCreditByOrder
     */
    private $getCreditByOrder;

    /**
     * @var CanPlace
     */
    private $canPlace;

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
        CanPlace $canPlace,
        CreateCreditEventInterface $createCreditEvent,
        AppendCreditEvent $appendCreditEvent,
        LoggerInterface $logger
    ) {
        $this->getCreditByOrder = $getCreditByOrder;
        $this->canPlace = $canPlace;
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

        if (!$this->canPlace->execute($order, $credit)) {
            throw new LocalizedException(__(
                'Company Store Credit cannot be used for this order
                because your order amount exceeds your credit amount.'
            ));
        }

        $creditEvent = $this->createCreditEvent->execute([
            CreditEventInterface::AMOUNT => $order->getBaseGrandTotal(),
            CreditEventInterface::TYPE => Operation::PLACE_ORDER,
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
            throw new LocalizedException(__('Can\'t place order with Company Store Credit'));
        }
    }
}
