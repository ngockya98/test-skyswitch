<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\CompanyExtensionInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Source\Company\Status;
use Magento\Customer\Api\Data\CustomerInterface as MagentoCustomerInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;

class Company extends \Magento\Framework\Model\AbstractExtensibleModel implements CompanyInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\CompanyAccount\Model\ResourceModel\Company::class);
    }

    /**
     * @inheritdoc
     */
    public function getCompanyId()
    {
        return (int)$this->_getData(CompanyInterface::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId($companyId)
    {
        $this->setData(CompanyInterface::COMPANY_ID, $companyId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyName()
    {
        return $this->_getData(CompanyInterface::COMPANY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyName($companyName)
    {
        $this->setData(CompanyInterface::COMPANY_NAME, $companyName);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(CompanyInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(CompanyInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLegalName()
    {
        return $this->_getData(CompanyInterface::LEGAL_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setLegalName($legalName)
    {
        $this->setData(CompanyInterface::LEGAL_NAME, $legalName);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyEmail()
    {
        return $this->_getData(CompanyInterface::COMPANY_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyEmail($companyEmail)
    {
        $this->setData(CompanyInterface::COMPANY_EMAIL, $companyEmail);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatTaxId()
    {
        return $this->_getData(CompanyInterface::VAT_TAX_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVatTaxId($vatTaxId)
    {
        $this->setData(CompanyInterface::VAT_TAX_ID, $vatTaxId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResellerId()
    {
        return $this->_getData(CompanyInterface::RESELLER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setResellerId($resellerId)
    {
        $this->setData(CompanyInterface::RESELLER_ID, $resellerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->_getData(CompanyInterface::COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->setData(CompanyInterface::COMMENT, $comment);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        $street = $this->_getData(CompanyInterface::STREET);
        if (!is_array($street)) {
            $street = explode("\n", $street ?? '');
        }

        return $street;
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        $this->setData(CompanyInterface::STREET, $street);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->_getData(CompanyInterface::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        $this->setData(CompanyInterface::CITY, $city);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountryId()
    {
        return $this->_getData(CompanyInterface::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId($countryId)
    {
        $this->setData(CompanyInterface::COUNTRY_ID, $countryId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegion()
    {
        return $this->_getData(CompanyInterface::REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion($region)
    {
        $this->setData(CompanyInterface::REGION, $region);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegionId()
    {
        return $this->_getData(CompanyInterface::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId($regionId)
    {
        $this->setData(CompanyInterface::REGION_ID, $regionId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPostcode()
    {
        return $this->_getData(CompanyInterface::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode($postcode)
    {
        $this->setData(CompanyInterface::POSTCODE, $postcode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTelephone()
    {
        return $this->_getData(CompanyInterface::TELEPHONE);
    }

    /**
     * @inheritdoc
     */
    public function setTelephone($telephone)
    {
        $this->setData(CompanyInterface::TELEPHONE, $telephone);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId()
    {
        return $this->_getData(CompanyInterface::CUSTOMER_GROUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->setData(CompanyInterface::CUSTOMER_GROUP_ID, $customerGroupId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSalesRepresentativeId()
    {
        return $this->_getData(CompanyInterface::SALES_REPRESENTATIVE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSalesRepresentativeId($salesRepresentativeId)
    {
        $this->setData(CompanyInterface::SALES_REPRESENTATIVE_ID, $salesRepresentativeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSuperUserId()
    {
        return (int)$this->_getData(CompanyInterface::SUPER_USER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSuperUserId($superUserId)
    {
        $this->setData(CompanyInterface::SUPER_USER_ID, $superUserId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRejectReason()
    {
        return $this->_getData(CompanyInterface::REJECT_REASON);
    }

    /**
     * @inheritdoc
     */
    public function setRejectReason($reason)
    {
        $this->setData(CompanyInterface::REJECT_REASON, $reason);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRejectAt()
    {
        return $this->_getData(CompanyInterface::REJECT_AT);
    }

    /**
     * @inheritdoc
     */
    public function setRejectAt($date)
    {
        $this->setData(CompanyInterface::REJECT_AT, $date);

        return $this;
    }

    /**
     * @return ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param CompanyExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        CompanyExtensionInterface $extensionAttributes
    ) {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->initExtensionAttributes();
        }

        return $this->_setExtensionAttributes($extensionAttributes);
    }

    private function initExtensionAttributes(): void
    {
        $extensionAttributes = $this->extensionAttributesFactory->create(Company::class, []);
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return array
     */
    public function getCustomerIds() : array
    {
        if ($this->getData(CompanyInterface::CUSTOMER_IDS) === null) {
            $customerIds = $this->getResource()->getCompanyCustomerIds($this);
            $this->setData(CompanyInterface::CUSTOMER_IDS, $customerIds);
            $this->setOrigData(CompanyInterface::CUSTOMER_IDS, $customerIds);
        }
        return $this->getData(CompanyInterface::CUSTOMER_IDS);
    }

    /**
     * @param array $customerIds
     * @return $this
     */
    public function setCustomerIds(array $customerIds = [])
    {
        $this->setData(CompanyInterface::CUSTOMER_IDS, $customerIds);
        return $this;
    }

    /**
     * @param MagentoCustomerInterface $customer
     * @return $this
     */
    public function addCustomer(MagentoCustomerInterface $customer)
    {
        if ($customer->getId() && !in_array($customer->getId(), $this->getCustomerIds())) {
            $customerIds = $this->getCustomerIds();
            $customerIds[] = $customer->getId();
            $this->setCustomerIds($customerIds);
        }

        return $this;
    }

    /**
     * @param MagentoCustomerInterface $customer
     * @return $this|CompanyInterface
     */
    public function removeCustomer(MagentoCustomerInterface $customer)
    {
        if ($customer->getId() && in_array($customer->getId(), $this->getCustomerIds())) {
            $customerIds = $this->getCustomerIds();
            $customerIds = array_diff($customerIds, [$customer->getId()]);
            $this->setCustomerIds($customerIds);
        }
        return $this;
    }

    /**
     * @param array $customerIds
     * @return $this
     */
    public function addCustomerIds(array $customerIds = [])
    {
        if (!empty($customerIds)) {
            $currentCustomerIds = $this->getCustomerIds();
            $this->setCustomerIds(array_merge($currentCustomerIds, $customerIds));
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == Status::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isRejected()
    {
        return $this->getStatus() == Status::STATUS_REJECTED;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() == Status::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function hasRoles()
    {
        return (bool)count($this->_resource->getCompanyRoleIds($this));
    }

    /**
     * @param array|string $payments
     * @return CompanyInterface
     */
    public function setRestrictedPayments($payments)
    {
        if (is_array($payments)) {
            $payments = implode(',', $payments);
        }

        $this->setData(CompanyInterface::RESTRICTED_PAYMENTS, $payments);

        return $this;
    }

    /**
     * @param bool $asArray
     * @return array|string
     */
    public function getRestrictedPayments($asArray = false)
    {
        $payments = $this->getData(CompanyInterface::RESTRICTED_PAYMENTS) ?: [];

        if ($asArray && !is_array($payments)) {
            $payments = explode(',', $payments);
        }

        return $payments;
    }

    /**
     * @return bool
     */
    public function getUseCompanyGroup()
    {
        return (bool)$this->getData(CompanyInterface::USE_COMPANY_GROUP);
    }

    /**
     * @param bool $flag
     * @return CompanyInterface
     */
    public function setUseCompanyGroup($flag = false)
    {
        $this->setData(CompanyInterface::USE_COMPANY_GROUP, (bool)$flag);

        return $this;
    }
}
