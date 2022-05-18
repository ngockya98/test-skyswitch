<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Profile;

class UpdateCompany extends SaveCompany
{
    public const RESOURCE = 'Amasty_CompanyAccount::edit_account';
    public const REDIRECT_URL = 'amasty_company/profile/edit';

    /**
     * @var string
     */
    protected $redirectUrl = self::REDIRECT_URL;

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isResourceAllow(static::RESOURCE);
    }
}
