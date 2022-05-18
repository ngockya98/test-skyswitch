<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Users;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Model\Company\CustomerNameResolver;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\ResourceModel\Customer;
use Amasty\CompanyAccount\Model\ResourceModel\Customer\CollectionFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Customer\Collection;
use Amasty\CompanyAccount\Model\UrlModifier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Amasty\CompanyAccount\Model\Source\Customer\Status as CustomerStatus;

class Grid extends \Magento\Framework\View\Element\Template
{
    public const AMASTY_COMPANY_USER_CREATE = 'amasty_company/user/create';
    public const AMASTY_COMPANY_USER_EDIT = 'amasty_company/user/edit';
    public const AMASTY_COMPANY_USER_STATUS_CHANGE = 'amasty_company/user/statusChange';
    public const AMASTY_COMPANY_USER_DELETE = 'amasty_company/user/delete';

    /**
     * @var string
     */
    protected $_template = 'Amasty_CompanyAccount::company/users/grid.phtml';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var Collection
     */
    private $users;

    /**
     * @var CustomerStatus
     */
    private $customerStatus;

    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * @var CustomerNameResolver
     */
    private $customerNameResolver;

    /**
     * @var UrlModifier
     */
    private $urlModifier;

    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        CompanyContext $companyContext,
        CustomerStatus $customerStatus,
        Customer $customerResource,
        CustomerNameResolver $customerNameResolver,
        UrlModifier $urlModifier,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->companyContext = $companyContext;
        $this->customerStatus = $customerStatus;
        $this->customerResource = $customerResource;
        $this->customerNameResolver = $customerNameResolver;
        $this->urlModifier = $urlModifier;
    }

    /**
     * @return Collection
     */
    public function getUsers()
    {
        if (!$this->users) {
            $company = $this->companyContext->getCurrentCompany();
            $this->users = $this->collectionFactory->create()->getCompanyCustomers((int)$company->getCompanyId());
        }

        return $this->users;
    }

    /**
     * @return string
     */
    public function getCreateNewUrl(): string
    {
        return $this->urlModifier->modify($this->_urlBuilder->getUrl(self::AMASTY_COMPANY_USER_CREATE));
    }

    /**
     * @param int $entityId
     * @return string
     */
    public function getEditUrl(int $entityId): string
    {
        return $this->urlModifier->modify(
            $this->_urlBuilder->getUrl(self::AMASTY_COMPANY_USER_EDIT, ['entity_id' => $entityId])
        );
    }

    /**
     * @param int $entityId
     * @return string
     */
    public function getChangeStatusUrl(int $entityId): string
    {
        return $this->_urlBuilder->getUrl(self::AMASTY_COMPANY_USER_STATUS_CHANGE, ['entity_id' => $entityId]);
    }

    /**
     * @param int $entityId
     * @return string
     */
    public function getDeleteUrl(int $entityId): string
    {
        return $this->_urlBuilder->getUrl(self::AMASTY_COMPANY_USER_DELETE, ['entity_id' => $entityId]);
    }

    /**
     * @param CustomerInterface $user
     * @return string
     */
    public function getName(CustomerInterface $user): string
    {
        try {
            $name = $this->customerNameResolver->getCustomerName($user->getEntityId());
        } catch (LocalizedException $e) {
            $name = '';
        }

        return $name;
    }

    /**
     * @param string|null $roleName
     * @return string|\Magento\Framework\Phrase
     */
    public function getRoleName($roleName)
    {
        return $roleName ?: __('Company Administrator');
    }

    /**
     * @param CustomerInterface $user
     * @return string
     */
    public function getStatusLabel(CustomerInterface $user)
    {
        return $this->customerStatus->getStatusLabelByValue((int)$user->getStatus());
    }

    /**
     * @return $this|Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getUsers()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amcompany.users.pager'
            )->setCollection(
                $this->getUsers()
            );
            $this->setChild('pager', $pager);
            $this->getUsers()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param string $resource
     * @return bool
     */
    public function isResourceAllowed(string $resource)
    {
        return $this->companyContext->isResourceAllow($resource);
    }
}
