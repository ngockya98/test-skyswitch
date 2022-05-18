<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\RoleInterface;

class Role extends \Magento\Framework\Model\AbstractExtensibleModel implements RoleInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\CompanyAccount\Model\ResourceModel\Role::class);
    }

    /**
     * @inheritdoc
     */
    public function getRoleId()
    {
        return (int)$this->_getData(RoleInterface::ROLE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRoleId($roleId)
    {
        $this->setData(RoleInterface::ROLE_ID, $roleId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoleName()
    {
        return $this->_getData(RoleInterface::ROLE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setRoleName($roleName)
    {
        $this->setData(RoleInterface::ROLE_NAME, $roleName);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyId()
    {
        return $this->_getData(RoleInterface::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId($companyId)
    {
        $this->setData(RoleInterface::COMPANY_ID, $companyId);

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleTypeId()
    {
        return $this->getData(RoleInterface::ROLE_TYPE_ID);
    }

    /**
     * @param int $roleId
     *
     * @return \Amasty\CompanyAccount\Api\Data\RoleInterface
     */
    public function setRoleTypeId($roleId)
    {
        $this->setData(RoleInterface::ROLE_TYPE_ID, $roleId);

        return $this;
    }

    /**
     * @return \Amasty\CompanyAccount\Api\Data\RoleExtensionInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param \Amasty\CompanyAccount\Api\Data\RoleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Amasty\CompanyAccount\Api\Data\RoleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
