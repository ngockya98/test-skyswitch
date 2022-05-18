<?php

namespace SkySwitch\Distributors\Block;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use SkySwitch\Distributors\Managers\DistributorManager;
use Magento\Sales\Block\Order\Totals;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class CheckoutSuccess extends Totals
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OrderFactory
     */
    protected $order_factory;

    /**
     * @var CollectionFactory
     */
    protected $order_collection_factory;

    /**
     * @var DistributorManager
     */
    protected $distributor_manager;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param OrderFactory $order_factory
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $order_collection_factory
     * @param DistributorManager $distributor_manager
     * @param array $data
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        OrderFactory $order_factory,
        Context $context,
        Registry $registry,
        CollectionFactory $order_collection_factory,
        DistributorManager $distributor_manager,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->order_factory = $order_factory;
        $this->order_collection_factory = $order_collection_factory;
        $this->distributor_manager = $distributor_manager;
    }

    /**
     * Return last order of customer
     *
     * @return mixed
     */
    public function getOrder()
    {
        $this->_order = $this->order_factory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
        return $this->_order;
    }

    /**
     * Return list order
     *
     * @return array
     */
    public function getOrders()
    {
        $splitted_orders = [];
        $last_order = $this->order_factory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
        $orders = $this->order_collection_factory->create()
            ->addAttributeToFilter('customer_email', $this->customerSession->getCustomer()->getData('email'))
            ->addAttributeToFilter('entity_id', ['gteq' => $last_order->getEntityId()])
            ->getItems();

        foreach ($orders as $order) {
            $splitted_orders[] = [
                'distributor' => $this->distributor_manager->getOrderDistributorName($order),
                'order_number' => $order->getIncrementId(),
                'order_id' => $order->getId()
            ];
        }

        return $splitted_orders;
    }

    /**
     * Return customer id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    /**
     * Return view order url
     *
     * @param int|string $orderId
     * @return mixed
     */
    public function getViewOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view/', ['order_id' => $orderId, '_secure' => true]);
    }
}
