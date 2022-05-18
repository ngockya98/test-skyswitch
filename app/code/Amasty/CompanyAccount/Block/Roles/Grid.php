<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Roles;

use Amasty\CompanyAccount\Api\RoleRepositoryInterface;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\UrlModifier;
use Magento\Framework\View\Element\Template;
use Amasty\CompanyAccount\Model\ResourceModel\Customer\CollectionFactory;

class Grid extends \Magento\Framework\View\Element\Template
{
    public const AMASTY_COMPANY_ROLE_EDIT = 'amasty_company/role/edit';
    public const AMASTY_COMPANY_ROLE_DELETE = 'amasty_company/role/delete';
    public const AMASTY_COMPANY_ROLE_CREATE = 'amasty_company/role/create';

    /**
     * @var string
     */
    protected $_template = 'Amasty_CompanyAccount::company/roles/grid.phtml';

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataInterface[]|array
     */
    private $roles = [];

    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var array
     */
    private $assocUsersQty = [];

    /**
     * @var UrlModifier
     */
    private $urlModifier;

    public function __construct(
        Template\Context $context,
        CompanyContext $companyContext,
        RoleRepositoryInterface $roleRepository,
        CollectionFactory $customerCollectionFactory,
        UrlModifier $urlModifier,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->companyContext = $companyContext;
        $this->roleRepository = $roleRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->urlModifier = $urlModifier;
    }

    /**
     * @return \Amasty\CompanyAccount\Model\ResourceModel\Role\Collection|array
     */
    public function getRoles()
    {
        if (!$this->roles) {
            $currentCompany = $this->companyContext->getCurrentCompany();
            $this->roles = $this->roleRepository->getRolesCollectionByCompanyId($currentCompany->getCompanyId());
        }

        return $this->roles;
    }

    /**
     * @param int $roleId
     * @return int
     */
    public function associatedUsersQty(int $roleId)
    {
        if (!isset($this->assocUsersQty[$roleId])) {
            $currentCompany = $this->companyContext->getCurrentCompany();
            $customers = $this->customerCollectionFactory->create()
                ->getCompanyCustomers($currentCompany->getCompanyId());
            foreach ($customers as $customer) {
                if ($customer->getRoleId()) {
                    if (!isset($this->assocUsersQty[$customer->getRoleId()])) {
                        $this->assocUsersQty[$customer->getRoleId()] = 0;
                    }
                    $this->assocUsersQty[$customer->getRoleId()] += 1;
                }
            }
        }

        return $this->assocUsersQty[$roleId] ?? 0;
    }

    /**
     * @param int $roleId
     * @return string
     */
    public function getEditUrl(int $roleId): string
    {
        return $this->urlModifier->modify(
            $this->_urlBuilder->getUrl(self::AMASTY_COMPANY_ROLE_EDIT, ['role_id' => $roleId])
        );
    }

    /**
     * @param int $roleId
     * @return string
     */
    public function getDeleteUrl(int $roleId): string
    {
        return $this->_urlBuilder->getUrl(self::AMASTY_COMPANY_ROLE_DELETE, ['role_id' => $roleId]);
    }

    /**
     * @return string
     */
    public function getCreateNewUrl(): string
    {
        return $this->urlModifier->modify($this->_urlBuilder->getUrl(self::AMASTY_COMPANY_ROLE_CREATE));
    }

    /**
     * @return $this|Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRoles()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amcompany.roles.pager'
            )->setCollection(
                $this->getRoles()
            );
            $this->setChild('pager', $pager);
            $this->getRoles()->load();
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
