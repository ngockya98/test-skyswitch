<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Sales\Controller\AbstractController;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use \Magento\Sales\Model\Order;

class OrderViewAuthorizationPlugin
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Order
     */
    private $orderModel;

    public function __construct(
        CompanyContext $companyContext,
        CustomerRepositoryInterface $customerRepository,
        \Amasty\CompanyAccount\Model\ResourceModel\Order $orderModel
    ) {
        $this->companyContext = $companyContext;
        $this->customerRepository = $customerRepository;
        $this->orderModel = $orderModel;
    }

    /**
     * @param OrderViewAuthorization $subject
     * @param bool $result
     * @param Order $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterCanView(OrderViewAuthorization $subject, bool $result, Order $order)
    {
        if (!$result) {
            $company = $this->orderModel->getCompanyIdByOrder((int) $order->getEntityId());
            $currentCompany = $this->companyContext->getCurrentCompany()->getCompanyId();

            return $company && $company == $currentCompany;
        }

        return $result;
    }
}
