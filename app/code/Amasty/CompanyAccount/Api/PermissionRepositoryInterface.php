<?php

namespace Amasty\CompanyAccount\Api;

use Amasty\CompanyAccount\Model\ResourceModel\Permission\Collection;

/**
 * @api
 */
interface PermissionRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\CompanyAccount\Api\Data\PermissionInterface $permission
     *
     * @return \Amasty\CompanyAccount\Api\Data\PermissionInterface
     */
    public function save(\Amasty\CompanyAccount\Api\Data\PermissionInterface $permission);

    /**
     * @param array $data
     * @return bool true on success
     */
    public function multipleSave(array $data);

    /**
     * @param int $roleId
     * @return Collection
     */
    public function getByRoleId(int $roleId);

    /**
     * Get by id
     *
     * @param int $permissionId
     *
     * @return \Amasty\CompanyAccount\Api\Data\PermissionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($permissionId);

    /**
     * Delete
     *
     * @param \Amasty\CompanyAccount\Api\Data\PermissionInterface $permission
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\CompanyAccount\Api\Data\PermissionInterface $permission);

    /**
     * Delete by id
     *
     * @param int $permissionId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($permissionId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
