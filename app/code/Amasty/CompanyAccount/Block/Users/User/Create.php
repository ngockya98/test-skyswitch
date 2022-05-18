<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Users\User;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\Source\Customer\Status as CustomerStatus;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Amasty\CompanyAccount\Api\RoleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Block\Form\Register;
use Magento\Customer\Model\ResourceModel\Customer as MagentoCustomerResource;
use Magento\Customer\Model\Customer as CustomerModel;

class Create extends \Magento\Framework\View\Element\Template
{
    public const AMASTY_COMPANY_SAVE_USER = 'amasty_company/user/saveUser';
    public const AMASTY_COMPANY_UPDATE_USER = 'amasty_company/user/updateUser';

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var CustomerStatus
     */
    private $customerStatus;

    /**
     * @var Register
     */
    private $registerBlock;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $user;

    /**
     * @var MagentoCustomerResource
     */
    private $magentoCustomerResource;

    /**
     * @var CustomerModel
     */
    private $customer;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Customer
     */
    private $customerResource;

    public function __construct(
        Template\Context $context,
        RoleRepositoryInterface $roleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CompanyContext $companyContext,
        CustomerStatus $customerStatus,
        Register $registerBlock,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        MagentoCustomerResource $magentoCustomerResource,
        CustomerModel $customer,
        \Amasty\CompanyAccount\Model\ResourceModel\Customer $customerResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->roleRepository = $roleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyContext = $companyContext;
        $this->customerStatus = $customerStatus;
        $this->registerBlock = $registerBlock;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->magentoCustomerResource = $magentoCustomerResource;
        $this->customer = $customer;
        $this->customerResource = $customerResource;
    }

    /**
     * @param int $userId
     * @return string
     */
    public function getSaveActionUrl(int $userId): string
    {
        return $userId
            ? $this->getUrl(self::AMASTY_COMPANY_UPDATE_USER)
            : $this->getUrl(self::AMASTY_COMPANY_SAVE_USER);
    }

    /**
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRolesList()
    {
        $this->searchCriteriaBuilder->addFilter(CompanyInterface::COMPANY_ID, $this->getCompanyId());

        return $this->roleRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyContext->getCurrentCompany()->getCompanyId();
    }

    /**
     * @param int $selectedRoleId
     * @param int $roleId
     * @param int $index
     * @return bool
     */
    public function isSelectedRole(int $selectedRoleId, int $roleId, int $index): bool
    {
        return $selectedRoleId ? $selectedRoleId == $roleId : $index == 1;
    }

    /**
     * @return array|array[]
     */
    public function getStatuses()
    {
        return $this->customerStatus->toOptionArray();
    }

    /**
     * @param string $type
     * @param string $field
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSpecialCustomAttributeHtml(string $type, string $field): string
    {
        $html = '';
        $block = $this->getLayout()->createBlock($type);
        if ($block->isEnabled()) {
            $user = $this->getCurrentUser();
            $action = 'get' . ucfirst($field);
            $data = $user->{$action}();
            $html = $block->setData('value', $data)->setData($field, $data)->toHtml();
        }

        return $html;
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function getCustomerAttributesHtml(int $customerId): string
    {
        $html = '';
        $customerAttributes = $this->getChildBlock('customer_form_user_attributes');
        if (class_exists(\Magento\CustomerCustomAttributes\Block\Form::class) && $customerAttributes) {
            $this->magentoCustomerResource->load($this->customer, $customerId);
            $customerAttributes->setEntity($this->customer)
                ->setObject($this->customer)
                ->setEntityType('customer')
                ->setShowContainer(false);
            $html = $customerAttributes->toHtml();
        }

        return $html;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getFormData()
    {
        return $this->registerBlock->getFormData();
    }

    /**
     * @param \Magento\Customer\Model\Metadata\Form $form
     * @return Register
     */
    public function restoreSessionData(\Magento\Customer\Model\Metadata\Form $form)
    {
        return $this->registerBlock->restoreSessionData($form);
    }

    /**
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface|DataObject|null
     */
    public function getUserCompanyData()
    {
        if ($this->getCurrentUser()
            && $this->getCurrentUser()->getExtensionAttributes()
            && $this->getCurrentUser()->getExtensionAttributes()->getAmcompanyAttributes()
        ) {
            $dataObject = $this->getCurrentUser()->getExtensionAttributes()->getAmcompanyAttributes();
        } else {
            $dataObject = new DataObject();
        }

        return $dataObject;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentUser()
    {
        if (!$this->user) {
            $customerId = $this->getRequest()->getParam('entity_id');
            try {
                $this->user = $this->customerRepository->getById($customerId);
            } catch (NoSuchEntityException $e) {
                $this->user = $this->customerFactory->create();
            }
        }

        return $this->user;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $user
     * @return bool
     */
    public function isCustomerActive($user)
    {
        return $this->companyContext->isCustomerActive($user);
    }
}
