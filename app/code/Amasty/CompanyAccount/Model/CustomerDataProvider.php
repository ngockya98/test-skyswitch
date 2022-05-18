<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Repository\CompanyRepository;
use Amasty\CompanyAccount\Model\ResourceModel\Customer;

class CustomerDataProvider
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    public function __construct(
        Customer $customer,
        CompanyRepository $companyRepository
    ) {
        $this->customer = $customer;
        $this->companyRepository = $companyRepository;
    }

    public function getCompanyNameByCustomerId(int $customerId): ?string
    {
        $company = $this->getCompanyByCustomerId($customerId);

        return $company ? $company->getCompanyName() : null;
    }

    public function getCompanyByCustomerId(int $customerId): ?CompanyInterface
    {
        $companyId = $this->getCompanyIdByCustomerId($customerId);

        return $companyId ? $this->companyRepository->getById($companyId) : null;
    }

    public function getCompanyIdByCustomerId(int $customerId): ?int
    {
        $companyId = $this->customer->getCompanyIdByCustomerId($customerId);
        return $companyId ? (int) $companyId : null;
    }
}
