<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Company\Profile;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Controller\Profile\SaveCompany;
use Amasty\CompanyAccount\Model\CountryDataProvider;
use Magento\Framework\Session\SessionManagerInterface as SessionManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Helper\Address;
use Magento\Directory\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Amasty\CompanyAccount\Model\CompanyContext;

class Create extends \Magento\Framework\View\Element\Template
{
    public const AMASTY_COMPANY_PROFILE_SAVE_COMPANY = 'amasty_company/profile/saveCompany';
    public const AMASTY_COMPANY_PROFILE_UPDATE_COMPANY = 'amasty_company/profile/updateCompany';

    /**
     * @var  CompanyInterface
     */
    private $currentCompany;

    /**
     * @var CountryDataProvider
     */
    private $countryDataProvider;

    /**
     * @var Address
     */
    private $addressHelper;

    /**
     * @var Data
     */
    private $directoryHelper;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    public function __construct(
        Context $context,
        CountryDataProvider $countryDataProvider,
        Address $addressHelper,
        Data $directoryHelper,
        CompanyContext $companyContext,
        SessionManagerInterface $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countryDataProvider = $countryDataProvider;
        $this->addressHelper = $addressHelper;
        $this->directoryHelper = $directoryHelper;
        $this->companyContext = $companyContext;
        $this->session = $session;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegionJson()
    {
        return $this->directoryHelper->getRegionJson();
    }

    /**
     * @return bool
     */
    public function isDisplayAllRegions()
    {
        return $this->_scopeConfig->isSetFlag(Data::XML_PATH_DISPLAY_ALL_STATES, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|string
     */
    public function getCountriesWithOptionalZip()
    {
        return $this->directoryHelper->getCountriesWithOptionalZip(true);
    }

    /**
     * @return string
     */
    public function getDefaultCountryId()
    {
        return $this->_scopeConfig->getValue(Data::XML_PATH_DEFAULT_COUNTRY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getCountriesList()
    {
        return $this->countryDataProvider->getCountriesList();
    }

    /**
     * @param string $attributeCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeValidationClass($attributeCode)
    {
        return $this->addressHelper->getAttributeValidationClass($attributeCode);
    }

    /**
     * @return string
     */
    public function getSaveActionUrl()
    {
        $route = $this->getCompanyValue(CompanyInterface::COMPANY_ID)
            ? self::AMASTY_COMPANY_PROFILE_UPDATE_COMPANY
            : self::AMASTY_COMPANY_PROFILE_SAVE_COMPANY;

        return $this->_urlBuilder->getUrl($route);
    }

    /**
     * @return CompanyInterface
     */
    public function getCurrentCompany()
    {
        if (!$this->currentCompany) {
            $this->currentCompany = $this->companyContext->getCurrentCompany();
            if ($this->currentCompany && !$this->currentCompany->getCompanyId()) {
                $this->currentCompany->addData($this->session->getData(SaveCompany::SESSION_NAME) ?: []);
            }
        }

        return $this->currentCompany;
    }

    /**
     * @param string $value
     * @return string
     */
    public function getCompanyValue(string $value)
    {
        $company = $this->getCurrentCompany();

        return $company && $company->getData($value) ? $company->getData($value) : '';
    }
}
