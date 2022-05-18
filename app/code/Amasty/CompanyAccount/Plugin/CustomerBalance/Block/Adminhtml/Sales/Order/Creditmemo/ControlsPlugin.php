<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo;

use Amasty\CompanyAccount\Model\Payment\ConfigProvider as PaymentConfigProvider;
use Magento\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo\Controls;
use Magento\Framework\Registry;

class ControlsPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Hide Refund to Store Credit field for Company Store Credit method.
     *
     * @param Controls $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanRefundToCustomerBalance(Controls $subject, bool $result): bool
    {
        $order = $this->registry->registry('current_creditmemo')->getOrder();

        return $result && $order->getPayment()->getMethod() !== PaymentConfigProvider::METHOD_NAME;
    }
}
