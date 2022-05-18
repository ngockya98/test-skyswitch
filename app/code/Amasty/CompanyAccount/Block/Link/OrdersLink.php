<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Link;

class OrdersLink extends AbstractLink
{
    /**
     * @return bool
     */
    protected function isAllowed(): bool
    {
        return $this->companyContext->isActiveOrInactiveCompany()
            && $this->companyContext->isCurrentUserCompanyUser()
            && parent::isAllowed();
    }
}
