<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Repository;

use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Amasty\CompanyAccount\Api\RoleRepositoryInterface;
use Amasty\CompanyAccount\Model\RoleFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Role as RoleResource;
use Amasty\CompanyAccount\Model\ResourceModel\Role\CollectionFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Role\Collection;
use Amasty\CompanyAccount\Model\Source\RoleType;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RoleRepository implements RoleRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var RoleResource
     */
    private $roleResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $roles;

    /**
     * @var CollectionFactory
     */
    private $roleCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        RoleFactory $roleFactory,
        RoleResource $roleResource,
        CollectionFactory $roleCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->roleFactory = $roleFactory;
        $this->roleResource = $roleResource;
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(RoleInterface $role)
    {
        try {
            if ($role->getRoleId()) {
                $role = $this->getById($role->getRoleId())->addData($role->getData());
            }
            $this->roleResource->save($role);
            unset($this->roles[$role->getRoleId()]);
        } catch (\Exception $e) {
            if ($role->getRoleId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save role with ID %1. Error: %2',
                        [$role->getRoleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new role. Error: %1', $e->getMessage()));
        }

        return $role;
    }

    /**
     * @inheritdoc
     */
    public function getById($roleId)
    {
        if (!isset($this->roles[$roleId])) {
            /** @var \Amasty\CompanyAccount\Model\Role $role */
            $role = $this->roleFactory->create();
            $this->roleResource->load($role, $roleId);
            if (!$role->getRoleId()) {
                throw new NoSuchEntityException(__('Role with specified ID "%1" not found.', $roleId));
            }
            $this->roles[$roleId] = $role;
        }

        return $this->roles[$roleId];
    }

    /**
     * @inheritdoc
     */
    public function delete(RoleInterface $role)
    {
        try {
            $this->roleResource->delete($role);
            unset($this->roles[$role->getRoleId()]);
        } catch (\Exception $e) {
            if ($role->getRoleId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove role with ID %1. Error: %2',
                        [$role->getRoleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove role. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($roleId)
    {
        $roleModel = $this->getById($roleId);
        $this->delete($roleModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\CompanyAccount\Model\ResourceModel\Role\Collection $roleCollection */
        $roleCollection = $this->roleCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $roleCollection);
        }

        $searchResults->setTotalCount($roleCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $roleCollection);
        }

        $roleCollection->setCurPage($searchCriteria->getCurrentPage());
        $roleCollection->setPageSize($searchCriteria->getPageSize());

        $roles = [];
        /** @var RoleInterface $role */
        foreach ($roleCollection->getItems() as $role) {
            $roles[] = $this->getById($role->getRoleId());
        }

        $searchResults->setItems($roles);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $roleCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $roleCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $roleCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $roleCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $roleCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $roleCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @param $companyId
     * @return $this
     * @throws CouldNotSaveException
     */
    public function createDefaultCompanyRoles($companyId)
    {
        $role = $this->roleFactory->create()
            ->setRoleTypeId(RoleType::TYPE_DEFAULT_USER)
            ->setCompanyId($companyId)
            ->setRoleName(__('Default User'));
        $this->save($role);

        return $this;
    }

    /**
     * @param int $companyId
     * @return Collection
     */
    public function getRolesCollectionByCompanyId(int $companyId)
    {
        $collection = $this->roleCollectionFactory->create();
        $collection->getSelect()->where('company_id = ?', $companyId);

        return $collection;
    }
}
