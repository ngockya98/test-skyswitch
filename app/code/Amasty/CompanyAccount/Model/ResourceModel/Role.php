<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Source\RoleType;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\CompanyAccount\Api\Data\RoleInterface;

class Role extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(RoleInterface::TABLE_NAME, RoleInterface::ROLE_ID);
    }

    /**
     * @param int $companyId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRoleIdsByCompanyId($companyId)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable(), 'role_id')
            ->where('company_id = ?', $companyId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param CompanyInterface $company
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyRoleIds(CompanyInterface $company)
    {
        return $this->getRoleIdsByCompanyId($company->getCompanyId());
    }

    /**
     * @param int $companyId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultUserRoleId($companyId)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable(), 'role_id')
            ->where('company_id = ?', $companyId)
            ->where('role_type_id = ?', RoleType::TYPE_DEFAULT_USER)
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }
}
