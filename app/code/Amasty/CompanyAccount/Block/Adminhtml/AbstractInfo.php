<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Adminhtml;

use Amasty\CompanyAccount\Model\CustomerDataProvider;
use Amasty\CompanyAccount\Model\ResourceModel\Order;
use Magento\Framework\View\Element\Template;

abstract class AbstractInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerDataProvider
     */
    protected $customerDataProvider;

    /**
     * @var Order
     */
    protected $orderModel;

    public function __construct(
        CustomerDataProvider $customerDataProvider,
        \Magento\Framework\Registry $registry,
        Template\Context $context,
        Order $orderModel,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerDataProvider = $customerDataProvider;
        $this->orderModel = $orderModel;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return __('Company Name')->render();
    }
}
