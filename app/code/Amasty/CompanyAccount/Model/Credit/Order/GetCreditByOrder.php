<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Order;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Query\GetByCompanyIdInterface as GetCreditByCompanyId;
use Amasty\CompanyAccount\Model\CustomerDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class GetCreditByOrder
{
    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @var GetCreditByCompanyId
     */
    private $getCreditByCompanyId;

    public function __construct(
        CustomerDataProvider $customerDataProvider,
        GetCreditByCompanyId $getCreditByCompanyId
    ) {
        $this->customerDataProvider = $customerDataProvider;
        $this->getCreditByCompanyId = $getCreditByCompanyId;
    }

    /**
     * @param OrderInterface $order
     * @return CreditInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(OrderInterface $order): CreditInterface
    {
        $company = $this->customerDataProvider->getCompanyByCustomerId((int) $order->getCustomerId());
        if ($company === null) {
            throw new LocalizedException(__('Customer not assigned for company.'));
        }

        return $this->getCreditByCompanyId->execute($company->getCompanyId());
    }
}
