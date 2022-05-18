<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Customer\Api;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Source\Company\Group;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\CompanyAccount\Api\Data\CustomerInterface as UserInterface;

class CustomerRepositoryPlugin
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Amasty\CompanyAccount\Api\Data\CustomerInterfaceFactory
     */
    private $companyCustomerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Company
     */
    private $companyResource;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\DataObject[]
     */
    private $origCompanyData;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        DataObjectHelper $dataObjectHelper,
        \Amasty\CompanyAccount\Model\ResourceModel\Customer $customerResource,
        \Amasty\CompanyAccount\Model\ResourceModel\Company $companyResource,
        \Amasty\CompanyAccount\Api\CompanyRepositoryInterface $companyRepository,
        \Amasty\CompanyAccount\Api\Data\CustomerInterfaceFactory $companyCustomerFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerResource = $customerResource;
        $this->companyResource = $companyResource;
        $this->companyRepository = $companyRepository;
        $this->companyCustomerFactory = $companyCustomerFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->logger = $logger;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        $this->addCompanyAttributes($customer);

        return $customer;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        $this->addCompanyAttributes($customer);

        return $customer;
    }

    /**
     * @param int $companyId
     * @return CompanyInterface|null
     */
    private function getCompanyById($companyId)
    {
        $company = null;

        if ($companyId) {
            try {
                $company = $this->companyRepository->getById($companyId);
            } catch (NoSuchEntityException $e) {
                $this->logger->critical(__('Company with id: %1 no longer exists', $companyId));
            }
        }

        return $company;
    }

    /**
     * @param CustomerInterface $customer
     * @param UserInterface $companyUser
     * @return $this
     * @throws LocalizedException
     */
    private function processCompany(CustomerInterface $customer, UserInterface $companyUser)
    {
        $companyId = $companyUser->getCompanyId();
        $origCompanyData = $this->getOrigCompanyData($customer->getId());
        $origCompanyId = $origCompanyData->getCompanyId();

        $company = $this->getCompanyById($companyId);

        if ($company) {
            if ($company->getId() != $origCompanyId) {
                $this->checkIsSuperUser($customer, $company);
                $company->addCustomer($customer);
                $this->companyRepository->save($company);
                $this->setGroupId($customer, $company, true);
            } else {
                $this->setGroupId($customer, $company);
            }
        } elseif ($origCompanyId) {
            $company = $this->getCompanyById($origCompanyId);
            if ($company) {
                $company->removeCustomer($customer);
                $this->companyRepository->save($company);
            }
        }

        return $this;
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param null $passwordHash
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundSave(
        CustomerRepositoryInterface $subject,
        callable $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        if (!$customer->getExtensionAttributes() || !$customer->getExtensionAttributes()->getAmcompanyAttributes()) {
            return $proceed($customer, $passwordHash);
        }

        $companyUser = $customer->getExtensionAttributes()->getAmcompanyAttributes();
        $customer = $proceed($customer, $passwordHash);
        $companyUser->setCustomerId($customer->getId());
        $this->processCompany($customer, $companyUser);
        $this->customerResource->saveAdvancedCustomerAttributes($companyUser);

        return $proceed($customer, $passwordHash);
    }

    /**
     * @param CustomerInterface $customer
     * @param CompanyInterface $company
     * @throws LocalizedException
     */
    private function checkIsSuperUser(CustomerInterface $customer, CompanyInterface $company)
    {
        $superUserIds = $this->companyResource->getAllSuperUserIds([$company->getCompanyId()]);
        if (in_array($customer->getId(), $superUserIds)) {
            throw new LocalizedException(
                __(
                    'This customer is already assigned to company "%1" as a company administrator',
                    $company->getCompanyName()
                )
            );
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param CompanyInterface $company
     * @param bool $force
     * @throws LocalizedException
     */
    private function setGroupId(CustomerInterface $customer, CompanyInterface $company, $force = false)
    {
        $companyGroupId = $company->getCustomerGroupId();
        if (!$companyGroupId || !$company->getUseCompanyGroup()) {
            return;
        }

        $customerGroupId = $customer->getGroupId();
        if ($customerGroupId
            && $customerGroupId != $companyGroupId
            && !$force
        ) {
            throw new LocalizedException(
                __(
                    'This customer assigned to company %1 and group change prohibited',
                    $company->getCompanyName()
                )
            );
        } else {
            $customer->setGroupId($companyGroupId);
        }
    }

    /**
     * @param CustomerRepositoryInterface $subject
     * @param callable $proceed
     * @param int $customerId
     * @return bool
     * @throws LocalizedException
     */
    public function aroundDeleteById(
        CustomerRepositoryInterface $subject,
        callable $proceed,
        $customerId
    ) {
        $result = false;

        $company = $this->companyRepository->getByField(CompanyInterface::SUPER_USER_ID, $customerId);
        if ($company->getId()) {
            throw new LocalizedException(
                __(
                    'You can\'t delete the Company Administrator.
                    In order to proceed please assign other Customer as Company Administrator of Company %1',
                    $company->getCompanyName()
                )
            );
        } else {
            $result = $proceed($customerId);
        }

        return $result;
    }

    /**
     * @param CustomerInterface $customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addCompanyAttributes(CustomerInterface $customer)
    {
        if ($customer->getExtensionAttributes()
            && $customer->getExtensionAttributes()->getAmCompanyAttributes()
        ) {
            return;
        }

        if (!$customer->getExtensionAttributes()) {
            $customerExtension = $this->extensionFactory->create(CustomerInterface::class);
            $customer->setExtensionAttributes($customerExtension);
        }

        $companyAttributes = $this->getCompanyAttributes($customer);

        if ($companyAttributes) {
            $customer->getExtensionAttributes()->setData('amcompany_attributes', $companyAttributes);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCompanyAttributes(CustomerInterface $customer)
    {
        $companyAttributesArray = $this->getCompanyAttributesArray((int)$customer->getId());
        if (!$companyAttributesArray) {
            return null;
        }
        $companyAttributes = $this->companyCustomerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $companyAttributes,
            $companyAttributesArray,
            \Amasty\CompanyAccount\Api\Data\CustomerInterface::class
        );
        $this->setOrigCompanyData($customer->getId(), $companyAttributesArray);

        return $companyAttributes;
    }

    /**
     * @param int $customerId
     * @param array $companyAttributesArray
     * @return $this
     */
    private function setOrigCompanyData($customerId, array $companyAttributesArray)
    {
        if ($customerId !== null) {
            $this->origCompanyData[$customerId] = $this->dataObjectFactory->create()
                ->setData($companyAttributesArray);
        }
        return $this;
    }

    /**
     * @param int $customerId
     * @return \Magento\Framework\DataObject
     */
    private function getOrigCompanyData($customerId = null)
    {
        return $this->origCompanyData[$customerId] ?? $this->dataObjectFactory->create();
    }

    /**
     * @param int $customerId
     * @return array
     */
    private function getCompanyAttributesArray(int $customerId)
    {
        try {
            $companyAttributesArray = $this->customerResource->getCustomerExtensionAttributes($customerId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $companyAttributesArray = [];
        }

        return $companyAttributesArray;
    }
}
