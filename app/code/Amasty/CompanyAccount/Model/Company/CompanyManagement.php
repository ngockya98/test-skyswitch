<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Company;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\PermissionInterface;
use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Amasty\CompanyAccount\Api\PermissionRepositoryInterface;
use Amasty\CompanyAccount\Model\Company;
use Amasty\CompanyAccount\Model\Repository\RoleRepository;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Exception\LocalizedException;

class CompanyManagement
{
    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Company
     */
    private $companyResource;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var ProviderInterface
     */
    private $aclProvider;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    /**
     * @var CustomerNameResolver
     */
    private $customerResolver;

    public function __construct(
        \Amasty\CompanyAccount\Model\Repository\RoleRepository $roleRepository,
        \Amasty\CompanyAccount\Model\ResourceModel\Customer $customerResource,
        \Amasty\CompanyAccount\Model\ResourceModel\Company $companyResource,
        ProviderInterface $aclProvider,
        PermissionRepositoryInterface $permissionRepository,
        CustomerNameResolver $customerResolver
    ) {
        $this->roleRepository = $roleRepository;
        $this->customerResource = $customerResource;
        $this->companyResource = $companyResource;
        $this->aclProvider = $aclProvider;
        $this->permissionRepository = $permissionRepository;
        $this->customerResolver = $customerResolver;
    }

    /**
     * @param CompanyInterface $company
     * @return $this
     */
    private function updateCompanyCustomerGroup(Company $company)
    {
        $customerGroupId = $company->getCustomerGroupId()
            ?: $this->customerResource->getGroupIdByCustomerId($company->getSuperUserId());

        if ($company->getCustomerGroupId() === null) {
            $this->companyResource->updateCompanyCustomerGroupId($company->getId(), $customerGroupId);
        }

        $this->customerResource->updateCustomerGroup(
            $customerGroupId,
            array_merge($company->getCustomerIds(), [$company->getSuperUserId()]),
            $company->getUseCompanyGroup()
        );

        return $this;
    }

    /**
     * @param Company $company
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    private function processCompanyCustomers(CompanyInterface $company)
    {
        $customerIds = array_unique($company->getCustomerIds());
        $this->checkCustomerIds($company, $customerIds);
        $origCustomerIds = $this->customerResource->getCustomerIdsByCompanyId($company->getCompanyId());

        if ($origCustomerIds != $customerIds) {
            $oldCustomerIds = array_diff($origCustomerIds, $customerIds);
            if ($oldCustomerIds) {
                $this->customerResource->unassignCompany($oldCustomerIds);
            }

            $newCustomers = array_diff($customerIds, $origCustomerIds);
            if ($newCustomers) {
                $this->customerResource->assignCompany($company->getCompanyId(), $newCustomers);
                $this->customerResource->updateCustomerGroup(
                    $company->getCustomerGroupId(),
                    $newCustomers,
                    $company->getUseCompanyGroup()
                );
            }
        }

        if ($company->isRejected()) {
            $this->customerResource->disableCustomers(array_replace($customerIds, $origCustomerIds));
            $this->customerResource->disableCustomers([$company->getSuperUserId()]);
        } else {
            $this->customerResource->enableCustomers([$company->getSuperUserId()]);
        }

        return $this;
    }

    /**
     * @param CompanyInterface $company
     * @param array $customerIds
     * @return $this
     * @throws LocalizedException
     */
    private function checkCustomerIds(CompanyInterface $company, array $customerIds = [])
    {
        $superUserIds = $this->companyResource->getAllSuperUserIds([$company->getCompanyId()]);
        $superUserIds = array_intersect($customerIds, $superUserIds);
        if (!empty($superUserIds)) {
            $customerId = current($superUserIds);
            throw new LocalizedException(
                __(
                    'The customer %1 (ID %2) is a Company Administrator and can’t be assigned to other company.',
                    $this->customerResolver->getCustomerName($customerId),
                    $customerId
                )
            );
        }

        return $this;
    }

    /**
     * @param CompanyInterface $company
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processCompanyAdministrator(CompanyInterface $company)
    {
        if ($company->getSuperUserId() != $company->getOrigData(CompanyInterface::SUPER_USER_ID)) {
            if ($company->getOrigData(CompanyInterface::SUPER_USER_ID)) {
                $this->customerResource->assignCompany(
                    $company->getCompanyId(),
                    [$company->getOrigData(CompanyInterface::SUPER_USER_ID)],
                    true
                );
                $company->addCustomerIds([$company->getOrigData(CompanyInterface::SUPER_USER_ID)]);
            }

            $this->customerResource->assignCompany($company->getCompanyId(), [$company->getSuperUserId()], false);
            $company->addCustomerIds([$company->getSuperUserId()]);
        }

        return $this;
    }

    /**
     * @param CompanyInterface $company
     * @return $this
     */
    private function processCompanyGroup(CompanyInterface $company)
    {
        $origGroupId = $company->getOrigData(CompanyInterface::CUSTOMER_GROUP_ID);
        if ($company->getCustomerGroupId() === null || $company->getCustomerGroupId() != $origGroupId) {
            $this->updateCompanyCustomerGroup($company);
        }

        return $this;
    }

    /**
     * @param CompanyInterface $company
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function processCompanyRoles(CompanyInterface $company)
    {
        if ($company->getCompanyId() != $company->getOrigData(CompanyInterface::COMPANY_ID)) {
            $this->roleRepository->createDefaultCompanyRoles($company->getId());
            $this->processCompanyPermissions($company);
        }

        return $this;
    }

    /**
     * @param CompanyInterface $company
     * @return CompanyManagement
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processCompany(CompanyInterface $company)
    {
        return $this->processCompanyRoles($company)
            ->processCompanyCustomers($company)
            ->processCompanyAdministrator($company)
            ->processCompanyGroup($company);
    }

    /**
     * @param CompanyInterface $company
     * @return $this
     * @throws LocalizedException
     */
    public function processCompanyDelete(CompanyInterface $company)
    {
        $customerIds = array_unique($company->getCustomerIds());
        if (!in_array($company->getSuperUserId(), $customerIds)) {
            $customerIds[] = $company->getSuperUserId();
        }

        $this->customerResource->disableCustomers($customerIds);

        return $this;
    }

    /**
     * @param CompanyInterface $company
     * @return $this
     */
    private function processCompanyPermissions(CompanyInterface $company)
    {
        $resources = $this->aclProvider->getAclResources();

        /**
         * @var RoleInterface $role
         */
        foreach ($this->roleRepository->getRolesCollectionByCompanyId($company->getCompanyId()) as $role) {
            $defaultRoleResources = $this->prepareDefaultUserPermissions($resources, $role);
            $this->permissionRepository->multipleSave($defaultRoleResources);
        }

        return $this;
    }

    /**
     * @param array $resources
     * @param RoleInterface $roleDefault
     * @return array
     */
    private function prepareDefaultUserPermissions(array $resources, RoleInterface $roleDefault)
    {
        $defaultRoleResources = [];
        $this->getResourcesArray($resources, $roleDefault->getRoleId(), $defaultRoleResources);
        foreach ($defaultRoleResources as $key => $resource) {
            if (strpos($resource[PermissionInterface::RESOURCE_ID], 'add') !== false
                || strpos($resource[PermissionInterface::RESOURCE_ID], 'edit') !== false
                || strpos($resource[PermissionInterface::RESOURCE_ID], 'delete') !== false
            ) {
                unset($defaultRoleResources[$key]);
            }
        }

        return $defaultRoleResources;
    }

    /**
     * @param array $resources
     * @param int $roleId
     * @param array $result
     */
    private function getResourcesArray(array $resources, int $roleId, &$result)
    {
        foreach ($resources as $resource) {
            $result[] = [
                PermissionInterface::ROLE_ID => $roleId,
                PermissionInterface::RESOURCE_ID => $resource['id']
            ];
            if ($resource['children']) {
                $this->getResourcesArray($resource['children'], $roleId, $result);
            }
        }
    }
}
