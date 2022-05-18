<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Order implements ArgumentInterface
{
    /**
     * @var \Amasty\CompanyAccount\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(\Amasty\CompanyAccount\Model\ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function getCustomerName(\Magento\Sales\Model\Order $order): string
    {
        if (null === $order->getCustomerFirstname()) {
            return (string)__('Guest');
        }

        $customerName = '';
        if ($this->configProvider->isVisibleCustomerPrefix() && $order->getCustomerPrefix()) {
            $customerName .= $order->getCustomerPrefix() . ' ';
        }
        $customerName .= $order->getCustomerFirstname();
        if ($this->configProvider->isVisibleCustomerMiddlename() && $order->getCustomerMiddlename()) {
            $customerName .= ' ' . $order->getCustomerMiddlename();
        }
        $customerName .= ' ' . $order->getCustomerLastname();
        if ($this->configProvider->isVisibleCustomerSuffix() && $order->getCustomerSuffix()) {
            $customerName .= ' ' . $order->getCustomerSuffix();
        }

        return $customerName;
    }
}
