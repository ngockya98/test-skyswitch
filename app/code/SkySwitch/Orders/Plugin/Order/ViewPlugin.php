<?php

namespace SkySwitch\Orders\Plugin\Order;

use Magento\Sales\Controller\AbstractController\View;
use SkySwitch\Orders\Managers\OrderManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class ViewPlugin
{
    /**
     * @var OrderManager
     */
    protected $order_manager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $order_repository;

    /**
     * @param OrderManager $order_manager
     * @param OrderRepositoryInterface $order_repository
     */
    public function __construct(OrderManager $order_manager, OrderRepositoryInterface $order_repository)
    {
        $this->order_manager = $order_manager;
        $this->order_repository = $order_repository;
    }

    /**
     * After plugin for execute method
     *
     * @param View $subject
     * @return void
     */
    public function beforeExecute(View $subject)
    {
        $order = $this->order_repository->get($subject->getRequest()->getParam('order_id'));
        $this->order_manager->getOrderDistributorInfo($order);
    }
}
