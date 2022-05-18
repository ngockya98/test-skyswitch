<?php

namespace Amasty\CompanyAccount\Api;

use Amasty\CompanyAccount\Model\ResourceModel\Role\Collection;

/**
 * @api
 */
interface RoleRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\CompanyAccount\Api\Data\RoleInterface $role
     *
     * @return \Amasty\CompanyAccount\Api\Data\RoleInterface
     */
    public function save(\Amasty\CompanyAccount\Api\Data\RoleInterface $role);

    /**
     * Get by id
     *
     * @param int $roleId
     *
     * @return \Amasty\CompanyAccount\Api\Data\RoleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($roleId);

    /**
     * Delete
     *
     * @param \Amasty\CompanyAccount\Api\Data\RoleInterface $role
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\CompanyAccount\Api\Data\RoleInterface $role);

    /**
     * Delete by id
     *
     * @param int $roleId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($roleId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $companyId
     * @return Collection
     */
    public function getRolesCollectionByCompanyId(int $companyId);
}
