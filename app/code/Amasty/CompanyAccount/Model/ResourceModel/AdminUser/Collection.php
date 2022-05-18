<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\AdminUser;

class Collection extends \Magento\User\Model\ResourceModel\User\Collection
{
    /**
     * @param array $userIds
     * @return $this
     */
    public function addIdsFilter($userIds = [])
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $this->getSelect()->where('main_table.user_id in (?)', $userIds);

        return $this;
    }
}
