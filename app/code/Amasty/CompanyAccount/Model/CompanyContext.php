<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\PermissionRepositoryInterface;
use Amasty\CompanyAccount\Model\Source\Company\Status as CompanyStatus;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Amasty\CompanyAccount\Api\Data\CompanyInterfaceFactory;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\SessionFactory;

class CompanyContext implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $currentCustomer;

    /**
     * @var \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    private $currentCompany;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $companyAdmin;

    /**
     * @var \Magento\User\Model\User
     */
    private $companyRepresentative;

    /**
     * @var CompanyInterfaceFactory
     */
    private $companyFactory;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var User
     */
    private $userResource;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        ConfigProvider $configProvider,
        HttpContext $httpContext,
        CustomerRepositoryInterface $customerRepository,
        SessionFactory $sessionFactory,
        CompanyInterfaceFactory $companyFactory,
        UserFactory $userFactory,
        CompanyRepositoryInterface $companyRepository,
        User $userResource,
        CustomerInterfaceFactory $customerFactory,
        LoggerInterface $logger,
        PermissionRepositoryInterface $permissionRepository,
        Session $customerSession
    ) {
        $this->configProvider = $configProvider;
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
        $this->sessionFactory = $sessionFactory;
        $this->companyRepository = $companyRepository;
        $this->companyFactory = $companyFactory;
        $this->userFactory = $userFactory;
        $this->userResource = $userResource;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->permissionRepository = $permissionRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @return bool
     */
    public function isCreateCompanyAllowed()
    {
        $user = $this->getCurrentCustomer();

        return $this->getCurrentCustomerId() && $this->isAllowedCustomerGroup() && !$this->isUserCompanyUser($user);
    }

    /**
     * @return int|null
     */
    public function getCurrentCustomerId()
    {
        return $this->sessionFactory->create()->getCustomer()->getId();
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function isCurrentCustomer(int $customerId): bool
    {
        return $this->getCurrentCustomerId() == $customerId;
    }

    /**
     * @return bool
     */
    public function isAllowedCustomerGroup()
    {
        $customerGroups = $this->configProvider->getAllowedCustomerGroups();
        $currentGroupId = $this->getCurrentCustomerGroupId();

        return $customerGroups && in_array($currentGroupId, explode(',', $customerGroups));
    }

    /**
     * @return bool
     */
    public function isCurrentUserCompanyUser(): bool
    {
        $currentCustomer = $this->getCurrentCustomer();

        return $currentCustomer && $this->isUserCompanyUser($currentCustomer);
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCurrentCustomer()
    {
        if (!$this->currentCustomer && $this->getCurrentCustomerId()) {
            try {
                $this->currentCustomer = $this->customerRepository->getById($this->getCurrentCustomerId());
            } catch (NoSuchEntityException $exception) {
                $this->logger->error($exception->getMessage());
                $this->currentCustomer = $this->customerFactory->create();
            }
        }

        return $this->currentCustomer;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     */
    public function isUserCompanyUser(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return (bool)$this->getUserCompanyId($customer);
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     */
    public function isCurrentCompanyUser(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $company = $this->getCurrentCompany();

        return $company && $this->isUserCompanyUser($customer)
            && ($this->getUserCompanyId($customer) == $company->getCompanyId());
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return int
     */
    public function getUserCompanyId(\Magento\Customer\Api\Data\CustomerInterface $customer): int
    {
        $companyId = 0;
        if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getAmCompanyAttributes()) {
            $companyId = (int)$customer->getExtensionAttributes()->getAmCompanyAttributes()->getCompanyId();
        }

        return $companyId;
    }

    /**
     * @return int
     */
    public function getCurrentCustomerGroupId()
    {
        return $this->customerSession->getCustomerGroupId()
            ?: \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     */
    public function isCustomerActive(\Magento\Customer\Api\Data\CustomerInterface $customer): bool
    {
        $isActive = true;
        if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getAmCompanyAttributes()) {
            $isActive = (bool)$customer->getExtensionAttributes()->getAmCompanyAttributes()->getStatus();
        }

        return $isActive;
    }

    /**
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function getCurrentCompany()
    {
        if (!$this->currentCompany) {
            try {
                $customer = $this->customerRepository->getById($this->getCurrentCustomerId());
                $this->currentCompany = $this->companyFactory->create();
                if ($this->isUserCompanyUser($customer)) {
                    $companyId = $customer->getExtensionAttributes()->getAmcompanyAttributes()->getCompanyId();
                    $this->currentCompany = $this->companyRepository->getById($companyId, true);
                }
            } catch (NoSuchEntityException $e) {
                $this->currentCompany = $this->companyFactory->create();
            }
        }

        return $this->currentCompany;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentCompanyAdmin()
    {
        return $this->getCompanyAdmin($this->getCurrentCompany());
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    public function getCurrentCompanyRepresentative()
    {
        return $this->getCompanyRepresentative($this->getCurrentCompany());
    }

    /**
     * @return bool
     */
    public function isActiveOrInactiveCompany()
    {
        return in_array(
            $this->getCurrentCompany()->getStatus(),
            [CompanyStatus::STATUS_ACTIVE, CompanyStatus::STATUS_INACTIVE]
        );
    }

    /**
     * @param \Amasty\CompanyAccount\Api\Data\CompanyInterface $company
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyAdmin(\Amasty\CompanyAccount\Api\Data\CompanyInterface $company)
    {
        if (!$this->companyAdmin) {
            try {
                $this->companyAdmin = $this->customerRepository->getById($company->getSuperUserId());
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
                $this->companyAdmin = $this->customerFactory->create();
            }
        }

        return $this->companyAdmin;
    }

    /**
     * @param $company
     * @return \Magento\User\Model\User|null
     */
    public function getCompanyRepresentative($company)
    {
        if (!$this->companyRepresentative) {
            $this->companyRepresentative = $this->userFactory->create();
            try {
                $this->userResource->load(
                    $this->companyRepresentative,
                    $company->getSalesRepresentativeId()
                );
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $this->companyRepresentative;
    }

    /**
     * @param string $resource
     * @return bool
     */
    public function isResourceAllow(string $resource)
    {
        $customer = $this->getCurrentCustomer();
        if ($customer->getExtensionAttributes()->getAmcompanyAttributes()) {
            $roleId = (int)$customer->getExtensionAttributes()->getAmcompanyAttributes()->getRoleId();
            $permissions = $this->permissionRepository->getByRoleId($roleId);
            $isAllowed = false;
            foreach ($permissions as $permission) {
                if ($resource == $permission->getResourceId()) {
                    $isAllowed = true;
                    break;
                }
            }

            return $roleId ? $isAllowed : true;
        }

        return true;
    }
}
