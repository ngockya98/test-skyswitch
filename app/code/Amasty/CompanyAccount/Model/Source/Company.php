<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Company implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param bool $withEmpty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray($withEmpty = true) : array
    {
        $companies = [];

        if ($withEmpty) {
            $companies[] = [
                'label' => __('Choose Company'),
                'value' => '',
            ];
        }

        $companyList = $this->companyRepository->getList($this->searchCriteriaBuilder->create());
        /**
         * @var CompanyInterface $company
         */
        foreach ($companyList->getItems() as $company) {
            $company->getExtensionAttributes();
            $companies[] = [
                'label' => $company->getCompanyName() ?: $company->getLegalName(),
                'value' => $company->getCompanyId(),
            ];
        }

        return $companies;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getElementOptionsArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $companyData) {
            $options[] = [
                'label' => $companyData['label'],
                'fieldvalue' => $companyData['value']
            ];
        }

        return $options;
    }
}
