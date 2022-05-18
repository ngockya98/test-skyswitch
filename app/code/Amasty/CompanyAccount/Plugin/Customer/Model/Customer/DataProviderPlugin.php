<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Customer\Model\Customer;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Model\ResourceModel\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProviderPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $repository;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var Customer
     */
    private $customerResource;

    public function __construct(
        CustomerRepositoryInterface $repository,
        CompanyRepositoryInterface $companyRepository,
        Customer $customerResource
    ) {
        $this->repository = $repository;
        $this->companyRepository = $companyRepository;
        $this->customerResource = $customerResource;
    }

    /**
     * @param AbstractDataProvider $subject
     * @param $data
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetData(AbstractDataProvider $subject, $data)
    {
        if (empty($data)) {
            return $data;
        }
        foreach ($data as $customerId => $fieldData) {
            if (!isset($fieldData['customer']['entity_id'])) {
                continue;
            }
            $customerId = $fieldData['customer']['entity_id'];
            $amcompanyAttributes['company_id'] = null;

            $amcompanyAttributes = [];
            try {
                $customer = $this->repository->getById($customerId);
                $companyAttributes = $customer->getExtensionAttributes()->getAmcompanyAttributes();
                if ($companyAttributes) {
                    $amcompanyAttributes = $companyAttributes->getData();
                    $company = $this->companyRepository->getById($companyAttributes->getCompanyId());
                    if ($company->getCompanyId()) {
                        $amcompanyAttributes['company_id'] = $companyAttributes->getCompanyId();
                        $isEditGroup = $company->getCustomerGroupId() && $company->getUseCompanyGroup();
                        $amcompanyAttributes['edit_group'] = $isEditGroup;
                        if ($customerId == $company->getSuperUserId()) {
                            $amcompanyAttributes['is_super_user'] = true;
                        }
                    }
                }
            } catch (NoSuchEntityException $e) {
                $amcompanyAttributes['company_id'] = null;
            }
            $fieldData['customer']['extension_attributes']['amcompany_attributes'] = $amcompanyAttributes;
            $data[$customerId] = $fieldData;
        }

        return $data;
    }
}
