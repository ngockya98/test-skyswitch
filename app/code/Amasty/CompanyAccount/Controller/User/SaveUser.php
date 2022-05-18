<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\User;

use Amasty\CompanyAccount\Api\Data\CustomerInterfaceFactory as CompanyCustomerFactory;
use Amasty\CompanyAccount\Model\ConfigProvider;
use Amasty\CompanyAccount\Model\ResourceModel\Customer;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class SaveUser extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::users_add';

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AccountManagementInterface
     */
    private $customerManager;

    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * @var CompanyCustomerFactory
     */
    private $companyCustomerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\GroupManagement
     */
    private $groupManagement;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        CustomerInterfaceFactory $customerFactory,
        DataObjectHelper $objectHelper,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerManager,
        Customer $customerResource,
        CompanyCustomerFactory $companyCustomerFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\GroupManagement $groupManagement,
        ConfigProvider $configProvider,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->customerFactory = $customerFactory;
        $this->objectHelper = $objectHelper;
        $this->storeManager = $storeManager;
        $this->customerManager = $customerManager;
        $this->customerResource = $customerResource;
        $this->companyCustomerFactory = $companyCustomerFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->groupManagement = $groupManagement;
        $this->configProvider = $configProvider;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/create');
        if (!$data) {
            return $resultRedirect;
        }
        try {
            if (isset($data['customer_id'])) {
                $this->updateUser($data);
                $successMessage = __('The customer was updated successfully.');
            } else {
                $this->createUser($data);
                $successMessage = __('The customer was created successfully.');
            }
            $this->messageManager->addSuccessMessage($successMessage);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred on the server. Your changes have not been saved.')
            );
            $this->logger->critical($e);
        }

        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/index');

        return $resultRedirect;
    }

    /**
     * @param array $data
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createUser(array $data)
    {
        $customer = $this->customerFactory->create();
        $this->saveCompanyInfo($customer, $data);
        $this->saveCustomer($customer, $data);

        return $customer;
    }

    /**
     * @param CustomerInterface $customer
     * @param array $data
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveCustomer(CustomerInterface $customer, array $data)
    {
        $this->objectHelper->populateWithArray($customer, $data, CustomerInterface::class);
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
        $customer->setGroupId($this->getCustomerGroup());

        return $this->customerManager->createAccount($customer);
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerGroup()
    {
        return $this->groupManagement->getDefaultGroup()->getId();
    }

    /**
     * @param CustomerInterface $customer
     * @param array $data
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveCompanyInfo(CustomerInterface $customer, array $data)
    {
        $companyCustomer = $this->companyCustomerFactory->create()->setData($data);
        $companyCustomer->setCompanyId($this->companyContext->getCurrentCompany()->getCompanyId());
        if (null === $customer->getExtensionAttributes()) {
            $extensionAttributes = $this->extensionAttributesFactory->create(CustomerInterface::class);
            $customer->setExtensionAttributes($extensionAttributes);
        }

        $customer->getExtensionAttributes()->setAmcompanyAttributes($companyCustomer);

        return $customer;
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function updateUser(array $data)
    {
        $customer = $this->customerRepository->getById($data['customer_id']);
        $this->setAdditionalData($customer, $data);
        $data = [
            'extension_attributes' => ['amcompany_attributes' => $data]
        ];
        $this->dataObjectHelper->populateWithArray($customer, $data, CustomerInterface::class);
        $this->customerRepository->save($customer);
        if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getAmcompanyAttributes()) {
            $companyAttr = $customer->getExtensionAttributes()->getAmcompanyAttributes();
            $this->dataObjectHelper->populateWithArray($companyAttr, $data, CustomerInterface::class);
            $this->customerResource->saveAdvancedCustomerAttributes($companyAttr);
        }
    }

    private function setAdditionalData(CustomerInterface $customer, array $params)
    {
        if ($this->configProvider->isShowPrefix() && isset($params['prefix'])) {
            $customer->setPrefix($params['prefix']);
        }
        if ($this->configProvider->isShowSuffix() && isset($params['suffix'])) {
            $customer->setSuffix($params['suffix']);
        }
        if (isset($params['firstname'])) {
            $customer->setFirstname($params['firstname']);
        }
        if (isset($params['lastname'])) {
            $customer->setLastname($params['lastname']);
        }
        if (isset($params['email'])) {
            $customer->setEmail($params['email']);
        }
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isActiveOrInactiveCompany()
            && parent::isAllowed();
    }
}
