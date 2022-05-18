<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Sales\Model\Service;

use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\CustomerDataProvider;
use Amasty\CompanyAccount\Model\ResourceModel\Order as CompanyAccountOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Service\OrderService;
use Amasty\CompanyAccount\Api\Data\OrderInterface as AmOrderInterface;

class OrderServicePlugin
{
    /**
     * @var CompanyAccountOrder
     */
    private $order;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    public function __construct(
        CompanyAccountOrder $order,
        CompanyContext $companyContext,
        CustomerDataProvider $customerDataProvider
    ) {
        $this->order = $order;
        $this->companyContext = $companyContext;
        $this->customerDataProvider = $customerDataProvider;
    }

    public function afterPlace(OrderService $subject, OrderInterface $order): OrderInterface
    {
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return $order;
        }

        $company = $this->companyContext->getCurrentCompany();
        if (!$company->getCompanyId()) {
            $company = $this->customerDataProvider->getCompanyByCustomerId((int)$customerId);
        }

        if (!$company) {
            return $order;
        }

        $this->order->saveData([
            AmOrderInterface::COMPANY_ORDER_ID => $order->getId(),
            AmOrderInterface::COMPANY_ID => $company->getCompanyId(),
            AmOrderInterface::COMPANY_NAME => $company->getCompanyName()
        ]);

        return $order;
    }
}
