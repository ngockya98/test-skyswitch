<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\PermissionInterface;

class Permission extends \Magento\Framework\Model\AbstractModel implements PermissionInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\CompanyAccount\Model\ResourceModel\Permission::class);
    }

    /**
     * @inheritdoc
     */
    public function getPermissionId()
    {
        return $this->_getData(PermissionInterface::PERMISSION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPermissionId($permissionId)
    {
        $this->setData(PermissionInterface::PERMISSION_ID, $permissionId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoleId()
    {
        return $this->_getData(PermissionInterface::ROLE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRoleId($roleId)
    {
        $this->setData(PermissionInterface::ROLE_ID, $roleId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResourceId()
    {
        return $this->_getData(PermissionInterface::RESOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setResourceId($resourceId)
    {
        $this->setData(PermissionInterface::RESOURCE_ID, $resourceId);

        return $this;
    }
}
