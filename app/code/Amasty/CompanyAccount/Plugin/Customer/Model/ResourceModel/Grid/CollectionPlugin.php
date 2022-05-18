<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Customer\Model\ResourceModel\Grid;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Magento\Customer\Model\ResourceModel\Grid\Collection;

class CollectionPlugin
{
    public function beforeAddFieldToFilter(Collection $subject, $field, $condition = null): array
    {
        if ($field == CompanyInterface::COMPANY_NAME) {
            $field = 'company.' . $field;
        }

        if ($field == 'main_table.' . CompanyInterface::COMPANY_NAME) {
            $field = 'company.' . CompanyInterface::COMPANY_NAME;
        }

        return [$field, $condition];
    }
}
