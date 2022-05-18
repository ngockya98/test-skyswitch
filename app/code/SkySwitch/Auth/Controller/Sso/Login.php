<?php

namespace SkySwitch\Auth\Controller\Sso;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use SkySwitch\Auth\Api\CustomerAttributeManagementInterface;
use SkySwitch\Auth\Api\Data\CustomerAttributesInterface;
use SkySwitch\Auth\Service\FusionAuth;
use SkySwitch\Auth\Model\FusionAuthProfile;
use SkySwitch\Auth\Service\SkySwitchService;

class Login implements HttpGetActionInterface
{
    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $redirectFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var FusionAuth
     */
    protected FusionAuth $fusionAuth;

    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @var CompanyRepositoryInterface
     */
    protected CompanyRepositoryInterface $companyRepository;

    /**
     * @var CustomerRepositoryInterface|CustomerRepository
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected CustomerInterfaceFactory $customerInterfaceFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var CustomerAttributeManagementInterface
     */
    protected CustomerAttributeManagementInterface $customerAttributeManager;

    /**
     * @var SkySwitchService
     */
    protected SkySwitchService $skySwitchApi;

    /**
     * @param Context $context
     * @param CompanyRepositoryInterface $companyRepository
     * @param CustomerRepository $customerRepository
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerFactory $customerFactory
     * @param FusionAuth $fusionAuth
     * @param RedirectFactory $redirectFactory
     * @param JsonFactory $jsonFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Session $customerSession
     * @param CustomerAttributeManagementInterface $customerManagement
     * @param SkySwitchService $skySwitchApi
     */
    public function __construct(
        Context                              $context,
        CompanyRepositoryInterface           $companyRepository,
        CustomerRepository                   $customerRepository,
        CustomerInterfaceFactory             $customerInterfaceFactory,
        CustomerFactory                      $customerFactory,
        FusionAuth                           $fusionAuth,
        RedirectFactory                      $redirectFactory,
        JsonFactory                          $jsonFactory,
        FilterBuilder                        $filterBuilder,
        SearchCriteriaBuilder                $searchCriteriaBuilder,
        Session                              $customerSession,
        CustomerAttributeManagementInterface $customerManagement,
        SkySwitchService                     $skySwitchApi
    ) {
        $this->context = $context;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->redirectFactory = $redirectFactory;
        $this->jsonFactory = $jsonFactory;
        $this->fusionAuth = $fusionAuth;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerSession = $customerSession;
        $this->customerAttributeManager = $customerManagement;
        $this->skySwitchApi = $skySwitchApi;
    }

    /**
     * Main execute sso login controller
     *
     * @return mixed
     */
    public function execute()
    {
        if (!$code = $this->context->getRequest()->getParam('code')) {
            return $this->redirectFactory->create()->setPath($this->fusionAuth->authUrl());
        }

        $profile = $this->fusionAuth->authenticate($code)->getProfile();

        $customer = $this->getCustomerFromFusionAuthProfile($profile);
        $logged_customer = $this->customerFactory->create()->setId($customer->getId());

        $this->customerSession->setCustomerAsLoggedIn($logged_customer);

        return $this->redirectFactory->create()->setPath('/');
    }

    /**
     * Return customer model using FusionAuth profile
     *
     * @param FusionAuthProfile $profile
     * @return mixed
     */
    protected function getCustomerFromFusionAuthProfile(FusionAuthProfile $profile)
    {
        $customer_id = $this->customerAttributeManager->getCustomerIdFromFusionAuthId($profile->getId());

        if ($customer_id) {
            return $this->customerRepository->getById($customer_id);
        }

        try {
            $customer = $this->customerRepository->get($profile->getEmail());
        } catch (NoSuchEntityException $exception) {
            $customer = $this->customerInterfaceFactory->create();
            $customer->setEmail($profile->getEmail());
        }

        $customer->setFirstname($profile->getFirstName());
        $customer->setLastname($profile->getLastName());

        $customer->getExtensionAttributes()->setFusionAuthId($profile->getId());
        $customer->getExtensionAttributes()->setResellerId($profile->getResellerId());

        return $this->customerRepository->save($customer);
    }

    /**
     * Return reseller model using FusionAuth profile
     *
     * If don't exist company, will create new using
     *
     * @param Customer $customer
     * @param FusionAuthProfile $profile
     * @return mixed
     */
    protected function getResellerFromCustomerProfile(Customer $customer, FusionAuthProfile $profile)
    {
        try {
            $attributes = $customer->getExtensionAttributes()->getAmCompanyAttributes();

            if (empty($attributes)) {
                throw new NoSuchEntityException();
            }

            return $this->companyRepository->getByField('reseller_id', $profile->getResellerId());
        } catch (NoSuchEntityException $exception) { //phpcs:ignore
            //@todo: don't do any things
        }

        $company = $this->companyRepository->getNew();
        $company->setResellerId($profile->getResellerId());
        return $this->companyRepository->save($company);
    }
}
