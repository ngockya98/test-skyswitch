<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Company;

use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Plugin\Checkout\Controller\Index\IndexPlugin;

class IsPaymentActiveForCurrentUser
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(CompanyContext $companyContext)
    {
        $this->companyContext = $companyContext;
    }

    /**
     * Check is payment method active for current user.
     *
     * @param string $methodCode
     * @return bool
     */
    public function execute(string $methodCode): bool
    {
        $result = true;

        $company = $this->companyContext->getCurrentCompany();

        if ($company && $company->getCompanyId()) {
            $result = $company->isActive()
                && $this->companyContext->isResourceAllow(IndexPlugin::RESOURCE)
                && !in_array(
                    $methodCode,
                    $company->getRestrictedPayments(true)
                );
        }

        return $result;
    }
}
