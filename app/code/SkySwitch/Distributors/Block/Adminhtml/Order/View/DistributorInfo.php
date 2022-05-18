<?php

namespace SkySwitch\Distributors\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use SkySwitch\Distributors\Managers\DistributorManager;
use SkySwitch\Orders\Managers\OrderManager;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;

class DistributorInfo extends AbstractOrder
{
    /**
     * @var OrderManager
     */
    protected $order_manager;

    /**
     * @param OrderManager $order_manager
     * @param Context $context
     * @param Registry $registry
     * @param Admin $admin
     */
    public function __construct(
        OrderManager $order_manager,
        Context $context,
        Registry $registry,
        Admin $admin
    ) {
        parent::__construct($context, $registry, $admin);

        $this->order_manager = $order_manager;
    }

    /**
     * Return distributor name
     *
     * @return mixed|string
     */
    public function getDistributorName()
    {
        return DistributorManager::getOrderDistributorName($this->getOrder());
    }

    /**
     * Return order tracking info
     *
     * @return mixed
     */
    public function getTrackingInfo()
    {
        return $this->order_manager->getTrackingInfo($this->getOrder());
    }

    /**
     * Return order provision info
     *
     * @return mixed
     */
    public function getProvisionInfo()
    {
        return $this->order_manager->getProvisioningInfo($this->getOrder());
    }
}
