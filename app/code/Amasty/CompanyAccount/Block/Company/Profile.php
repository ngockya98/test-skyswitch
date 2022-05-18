<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Company;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Amasty\CompanyAccount\Model\CountryDataProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Amasty\CompanyAccount\Model\Source\Company\Status as CompanyStatus;
use Amasty\CompanyAccount\Controller\Profile\Edit;

class Profile extends \Magento\Framework\View\Element\Template
{
    public const AMASTY_COMPANY_PROFILE_CREATE = 'amasty_company/profile/create';
    public const AMASTY_COMPANY_PROFILE_EDIT = 'amasty_company/profile/edit';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CountryDataProvider
     */
    private $countryDataProvider;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    /**
     * @var CompanyStatus
     */
    private $companyStatus;

    public function __construct(
        Template\Context $context,
        UrlInterface $urlBuilder,
        CompanyContext $companyContext,
        CustomerRepositoryInterface $customerRepository,
        CountryDataProvider $countryDataProvider,
        CustomerNameGenerationInterface $customerNameGeneration,
        CompanyStatus $companyStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->companyContext = $companyContext;
        $this->customerRepository = $customerRepository;
        $this->countryDataProvider = $countryDataProvider;
        $this->customerNameGeneration = $customerNameGeneration;
        $this->companyStatus = $companyStatus;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        $route = $this->isCurrentUserCompanyUser()
            ? self::AMASTY_COMPANY_PROFILE_EDIT
            : self::AMASTY_COMPANY_PROFILE_CREATE;

        return $this->urlBuilder->getUrl($route);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdminName()
    {
        $customer = $this->companyContext->getCurrentCompanyAdmin();

        return $customer ? $this->customerNameGeneration->getCustomerName($customer) : '';
    }

    /**
     * @return string
     */
    public function getCompanyStatus()
    {
        $company = $this->companyContext->getCurrentCompany();

        return $this->companyStatus->getStatusLabelByValue($company->getStatus());
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getStatusComment()
    {
        $company = $this->companyContext->getCurrentCompany();
        switch ($company->getStatus()) {
            case CompanyStatus::STATUS_PENDING:
                $comment = __('Your company account request is being reviewed by admin.');
                break;
            case CompanyStatus::STATUS_INACTIVE:
                $comment = __('Your company account got inactivated by admin. All company users are free to'
                    . ' log in to their customer accounts, but can’t place orders. ');
                break;
            default:
                $comment = '';
        }

        return $comment;
    }

    /**
     * @return bool
     */
    public function isShowRepresentative()
    {
        $company = $this->companyContext->getCurrentCompany();

        return in_array($company->getStatus(), [CompanyStatus::STATUS_ACTIVE, CompanyStatus::STATUS_INACTIVE]);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdminJobTitle()
    {
        $jobTitle = '';
        $customer = $this->companyContext->getCurrentCompanyAdmin();
        if ($customer) {
            $companyAttributes = $customer->getExtensionAttributes()->getAmCompanyAttributes();
            $jobTitle = $companyAttributes ? $companyAttributes->getJobTitle() : '';
        }

        return $jobTitle;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAdminEmail()
    {
        return $this->companyContext->getCurrentCompanyAdmin()->getEmail();
    }

    /**
     * @return bool
     */
    public function isCurrentUserCompanyUser()
    {
        return $this->companyContext->isCurrentUserCompanyUser();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryLabel()
    {
        return $this->countryDataProvider->getCountryNameByCode($this->getCurrentCompany()->getCountryId());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAddressLabel()
    {
        $company = $this->getCurrentCompany();
        $address = [
            $company->getCity(),
            $this->countryDataProvider->getRegionName(
                $company->getCountryId(),
                $company->getRegionId(),
                $company->getRegion()
            ),
            $company->getPostcode()
        ];

        return implode(', ', $address);
    }

    /**
     * @return string
     */
    public function getStreetLabel()
    {
        $company = $this->getCurrentCompany();
        $street = $company->getStreet();
        $streetLabel = '';
        $streetLabel .= !empty($street[0]) ? $street[0] : '';
        $streetLabel .= !empty($street[1]) ? ' ' . $street[1] : '';

        return $streetLabel;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTelephoneLabel()
    {
        return __('T: %1', $this->getCurrentCompany()->getTelephone());
    }

    /**
     * @return string
     */
    public function getCompanyNameLabel()
    {
        $company = $this->getCurrentCompany();
        $companyName = $company->getCompanyName();
        $companyName .= $company->getLegalName() ? ' (' . $company->getLegalName() . ')' : '';

        return $companyName;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getVatTaxLabel()
    {
        return __('VAT/TAX ID: %1', $this->getCurrentCompany()->getVatTaxId());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getResellerIdLabel()
    {
        return __('Re-seller ID: %1', $this->getCurrentCompany()->getResellerId());
    }

    /**
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function getCurrentCompany()
    {
        return $this->companyContext->getCurrentCompany();
    }

    /**
     * @return string
     */
    public function getSalesRepresentativeName()
    {
        return $this->companyContext->getCurrentCompanyRepresentative()->getName();
    }

    /**
     * @return mixed|string|null
     */
    public function getSalesRepresentativeEmail()
    {
        return $this->companyContext->getCurrentCompanyRepresentative()->getEmail();
    }

    /**
     * @return bool
     */
    public function isEditAllowed()
    {
        return $this->companyContext->isResourceAllow(Edit::RESOURCE);
    }
}
