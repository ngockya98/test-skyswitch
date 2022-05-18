<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Repository;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Model\Company\CompanyManagement;
use Amasty\CompanyAccount\Model\CompanyFactory;
use Amasty\CompanyAccount\Model\MailManager;
use Amasty\CompanyAccount\Model\ResourceModel\Company as CompanyResource;
use Amasty\CompanyAccount\Model\ResourceModel\Company\CollectionFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Company\Collection;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\EntityManager\Operation\Update\UpdateExtensions;
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
class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CompanyFactory
     */
    private $companyFactory;

    /**
     * @var CompanyResource
     */
    private $companyResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $companies;

    /**
     * @var CollectionFactory
     */
    private $companyCollectionFactory;

    /**
     * @var CompanyManagement
     */
    private $companyManagement;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var UpdateExtensions
     */
    private $updateExtensions;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        CompanyFactory $companyFactory,
        CompanyResource $companyResource,
        CollectionFactory $companyCollectionFactory,
        CompanyManagement $companyManagement,
        MailManager $mailManager,
        ReadExtensions $readExtensions,
        UpdateExtensions $updateExtensions
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->companyFactory = $companyFactory;
        $this->companyResource = $companyResource;
        $this->companyCollectionFactory = $companyCollectionFactory;
        $this->companyManagement = $companyManagement;
        $this->mailManager = $mailManager;
        $this->readExtensions = $readExtensions;
        $this->updateExtensions = $updateExtensions;
    }

    /**
     * @inheritdoc
     */
    public function save(CompanyInterface $company)
    {
        $isCompanyExisted = (bool)$company->getCompanyId();
        $oldCompany = $this->getByField('company_id', $company->getCompanyId(), true);
        if ($isCompanyExisted) {
            $company->setRegionId($company->getRegionId() ?: null);
            $company->setRegion($company->getRegion() ?: null);
            $company = $this->getById($company->getCompanyId())->addData($company->getData());
        }
        try {
            $this->companyResource->save($company);
            $this->updateExtensions->execute($company);
        } catch (\Exception $e) {
            if ($company->getCompanyId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save company with ID %1. Error: %2',
                        [$company->getCompanyId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new company. Error: %1', $e->getMessage()));
        }

        $this->companyManagement->processCompany($company);
        $this->mailManager->sendCompanyEmails($isCompanyExisted, $company, $oldCompany);
        unset($this->companies[$company->getCompanyId()]);

        return $company;
    }

    /**
     * @inheritdoc
     */
    public function getById($companyId, bool $withExtensions = false)
    {
        if (!isset($this->companies[$companyId])) {
            /** @var \Amasty\CompanyAccount\Model\Company $company */
            $company = $this->companyFactory->create();
            $this->companyResource->load($company, $companyId);
            if (!$company->getCompanyId()) {
                throw new NoSuchEntityException(__('Company with specified ID "%1" not found.', $companyId));
            }
            $this->companies[$companyId] = $company;
            if ($withExtensions) {
                $this->readExtensions->execute($company);
            }
        }

        return $this->companies[$companyId];
    }

    public function getNew(bool $withExtensions = false): \Amasty\CompanyAccount\Api\Data\CompanyInterface
    {
        $company = $this->companyFactory->create();
        if ($withExtensions) {
            $this->readExtensions->execute($company);
        }

        return $company;
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @param bool $withExtensions
     * @return \Amasty\CompanyAccount\Model\Company
     */
    public function getByField($fieldName, $value, bool $withExtensions = false)
    {
        /** @var \Amasty\CompanyAccount\Model\Company $company */
        $company = $this->companyFactory->create();
        $this->companyResource->load($company, $value, $fieldName);
        if ($withExtensions) {
            $this->readExtensions->execute($company);
        }

        return $company;
    }

    /**
     * @inheritdoc
     */
    public function delete(CompanyInterface $company)
    {
        try {
            $this->companyManagement->processCompanyDelete($company);
            $this->companyResource->delete($company);
            unset($this->companies[$company->getCompanyId()]);
        } catch (\Exception $e) {
            if ($company->getCompanyId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove company with ID %1. Error: %2',
                        [$company->getCompanyId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove company. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($companyId)
    {
        $companyModel = $this->getById($companyId);
        $this->delete($companyModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\CompanyAccount\Model\ResourceModel\Company\Collection $companyCollection */
        $companyCollection = $this->companyCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $companyCollection);
        }

        $searchResults->setTotalCount($companyCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $companyCollection);
        }

        $companyCollection->setCurPage($searchCriteria->getCurrentPage());
        $companyCollection->setPageSize($searchCriteria->getPageSize());

        $companys = [];
        /** @var CompanyInterface $company */
        foreach ($companyCollection->getItems() as $company) {
            $companys[] = $this->getById($company->getCompanyId());
        }

        $searchResults->setItems($companys);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $companyCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $companyCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $companyCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $companyCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $companyCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $companyCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
