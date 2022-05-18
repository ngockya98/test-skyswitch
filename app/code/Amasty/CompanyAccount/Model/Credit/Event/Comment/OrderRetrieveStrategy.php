<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Event\Comment;

use Magento\Framework\UrlInterface;
use Magento\Sales\Model\OrderFactory;

class OrderRetrieveStrategy implements RetrieveStrategyInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        UrlInterface $urlBuilder,
        OrderFactory $orderFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->orderFactory = $orderFactory;
    }

    public function execute(string $value): string
    {
        return __(
            'Order: <a href="%1">#%2</a>',
            $this->urlBuilder->getUrl('sales/order/view', [
                'order_id' => $this->getOrderIdByIncrement($value)
            ]),
            $value
        )->render();
    }

    private function getOrderIdByIncrement(string $incrementId): int
    {
        $order = $this->orderFactory->create();
        $order->loadByIncrementId($incrementId);

        return (int) $order->getEntityId();
    }
}
