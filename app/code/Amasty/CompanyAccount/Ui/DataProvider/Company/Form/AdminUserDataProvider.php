<?php

declare(strict_types=1);

namespace  Amasty\CompanyAccount\Ui\DataProvider\Company\Form;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;

class AdminUserDataProvider extends UserDataProvider
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExcludeCustomerIds()
    {
        $companyId = $this->request->getParam(CompanyInterface::COMPANY_ID);
        if ($companyId) {
            return $this->companyResource->getAllSuperUserIds([$companyId]);
        }

        return parent::getExcludeCustomerIds();
    }
}
