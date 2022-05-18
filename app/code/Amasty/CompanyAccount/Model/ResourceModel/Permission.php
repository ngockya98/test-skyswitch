<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\PermissionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Permission extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(PermissionInterface::TABLE_NAME, PermissionInterface::PERMISSION_ID);
    }

    /**
     * @param array $rows
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function multipleSave(array $rows)
    {
        $roleId = $rows[0]['role_id'];
        $this->getConnection()->delete($this->getMainTable(), ['role_id = ?' => $roleId]);
        $this->getConnection()->insertMultiple($this->getMainTable(), $rows);
    }
}
