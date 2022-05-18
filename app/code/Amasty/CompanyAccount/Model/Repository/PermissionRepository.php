<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Repository;

use Amasty\CompanyAccount\Api\Data\PermissionInterface;
use Amasty\CompanyAccount\Api\PermissionRepositoryInterface;
use Amasty\CompanyAccount\Model\PermissionFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Permission as PermissionResource;
use Amasty\CompanyAccount\Model\ResourceModel\Permission\CollectionFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Permission\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var PermissionFactory
     */
    private $permissionFactory;

    /**
     * @var PermissionResource
     */
    private $permissionResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $permissions;

    /**
     * @var CollectionFactory
     */
    private $permissionCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        PermissionFactory $permissionFactory,
        PermissionResource $permissionResource,
        CollectionFactory $permissionCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->permissionFactory = $permissionFactory;
        $this->permissionResource = $permissionResource;
        $this->permissionCollectionFactory = $permissionCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function save(PermissionInterface $permission)
    {
        try {
            if ($permission->getPermissionId()) {
                $permission = $this->getById($permission->getPermissionId())->addData($permission->getData());
            }
            $this->permissionResource->save($permission);
            unset($this->permissions[$permission->getPermissionId()]);
        } catch (\Exception $e) {
            if ($permission->getPermissionId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save permission with ID %1. Error: %2',
                        [$permission->getPermissionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new permission. Error: %1', $e->getMessage()));
        }

        return $permission;
    }

    /**
     * @param array $data
     * @return bool true on success
     */
    public function multipleSave(array $data)
    {
        $isSuccess = true;
        try {
            $this->permissionResource->multipleSave($data);
        } catch (\Exception $e) {
            $isSuccess = false;
            $this->logger->error($e->getMessage());
        }

        return $isSuccess;
    }

    /**
     * @param int $roleId
     * @return Collection
     */
    public function getByRoleId(int $roleId)
    {
        $collection = $this->permissionCollectionFactory->create();
        $collection->addFieldToFilter(PermissionInterface::ROLE_ID, $roleId);

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getById($permissionId)
    {
        if (!isset($this->permissions[$permissionId])) {
            /** @var \Amasty\CompanyAccount\Model\Permission $permission */
            $permission = $this->permissionFactory->create();
            $this->permissionResource->load($permission, $permissionId);
            if (!$permission->getPermissionId()) {
                throw new NoSuchEntityException(__('Permission with specified ID "%1" not found.', $permissionId));
            }
            $this->permissions[$permissionId] = $permission;
        }

        return $this->permissions[$permissionId];
    }

    /**
     * @inheritdoc
     */
    public function delete(PermissionInterface $permission)
    {
        try {
            $this->permissionResource->delete($permission);
            unset($this->permissions[$permission->getPermissionId()]);
        } catch (\Exception $e) {
            if ($permission->getPermissionId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove permission with ID %1. Error: %2',
                        [$permission->getPermissionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove permission. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($permissionId)
    {
        $permissionModel = $this->getById($permissionId);
        $this->delete($permissionModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\CompanyAccount\Model\ResourceModel\Permission\Collection $permissionCollection */
        $permissionCollection = $this->permissionCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $permissionCollection);
        }

        $searchResults->setTotalCount($permissionCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $permissionCollection);
        }

        $permissionCollection->setCurPage($searchCriteria->getCurrentPage());
        $permissionCollection->setPageSize($searchCriteria->getPageSize());

        $permissions = [];
        /** @var PermissionInterface $permission */
        foreach ($permissionCollection->getItems() as $permission) {
            $permissions[] = $this->getById($permission->getPermissionId());
        }

        $searchResults->setItems($permissions);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $permissionCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $permissionCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $permissionCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $permissionCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $permissionCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $permissionCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
