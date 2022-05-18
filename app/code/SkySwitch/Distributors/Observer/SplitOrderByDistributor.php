<?php

namespace SkySwitch\Distributors\Observer;

use Magento\Framework\Event\Observer;
use SkySwitch\Distributors\Managers\DistributorManager;
use SkySwitch\Orders\Managers\OrderManager;

class SplitOrderByDistributor implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var OrderManager
     */
    protected $order_manager;

    /**
     * @var DistributorManager
     */
    protected $distributor_manager;

    /**
     * @param OrderManager $order_manager
     * @param DistributorManager $distributor_manager
     */
    public function __construct(
        OrderManager $order_manager,
        DistributorManager $distributor_manager
    ) {
        $this->order_manager = $order_manager;
        $this->distributor_manager = $distributor_manager;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        $orders = $this->order_manager->splitOrdersByDistributor($order);
        $this->distributor_manager->createDistributorOrders($orders);
    }
}
