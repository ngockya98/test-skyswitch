<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Link;

class CompanyAccountLink extends AbstractLink
{
    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCreateCompanyAllowed()
            || ($this->companyContext->isCurrentUserCompanyUser() && parent::isAllowed());
    }
}
