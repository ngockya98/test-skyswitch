<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Link;

class UsersLink extends AbstractLink
{
    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isActiveOrInactiveCompany()
            && $this->companyContext->isCurrentUserCompanyUser()
            && parent::isAllowed();
    }
}
