<?php

namespace Amasty\CompanyAccount\Api\Data;

interface PermissionInterface
{
    public const TABLE_NAME = 'amasty_company_account_permission';
    public const PERMISSION_ID = 'permission_id';
    public const ROLE_ID = 'role_id';
    public const RESOURCE_ID = 'resource_id';

    /**
     * @return int
     */
    public function getPermissionId();

    /**
     * @param int $permissionId
     *
     * @return \Amasty\CompanyAccount\Api\Data\PermissionInterface
     */
    public function setPermissionId($permissionId);

    /**
     * @return int
     */
    public function getRoleId();

    /**
     * @param int $roleId
     *
     * @return \Amasty\CompanyAccount\Api\Data\PermissionInterface
     */
    public function setRoleId($roleId);

    /**
     * @return string
     */
    public function getResourceId();

    /**
     * @param string $resourceId
     *
     * @return \Amasty\CompanyAccount\Api\Data\PermissionInterface
     */
    public function setResourceId($resourceId);
}
