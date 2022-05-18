<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface as MagentoCustomerInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface CompanyInterface extends ExtensibleDataInterface
{
    public const TABLE_NAME = 'amasty_company_account_company';
    public const COMPANY_ID = 'company_id';
    public const COMPANY_NAME = 'company_name';
    public const STATUS = 'status';
    public const LEGAL_NAME = 'legal_name';
    public const COMPANY_EMAIL = 'company_email';
    public const VAT_TAX_ID = 'vat_tax_id';
    public const RESELLER_ID = 'reseller_id';
    public const COMMENT = 'comment';
    public const STREET = 'street';
    public const CITY = 'city';
    public const COUNTRY_ID = 'country_id';
    public const REGION = 'region';
    public const REGION_ID = 'region_id';
    public const POSTCODE = 'postcode';
    public const TELEPHONE = 'telephone';
    public const CUSTOMER_GROUP_ID = 'customer_group_id';
    public const SALES_REPRESENTATIVE_ID = 'sales_representative_id';
    public const SUPER_USER_ID = 'super_user_id';
    public const REJECT_REASON = 'reject_reason';
    public const REJECT_AT = 'rejected_at';
    public const CUSTOMER_IDS = 'customer_ids';
    public const RESTRICTED_PAYMENTS = 'restricted_payments';
    public const USE_COMPANY_GROUP = 'use_company_group';

    /**
     * @return int
     */
    public function getCompanyId();

    /**
     * @param int $companyId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCompanyId($companyId);

    /**
     * @return string|null
     */
    public function getCompanyName();

    /**
     * @param string|null $companyName
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCompanyName($companyName);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getLegalName();

    /**
     * @param string|null $legalName
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setLegalName($legalName);

    /**
     * @return string|null
     */
    public function getCompanyEmail();

    /**
     * @param string|null $companyEmail
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCompanyEmail($companyEmail);

    /**
     * @return string|null
     */
    public function getVatTaxId();

    /**
     * @param string|null $vatTaxId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setVatTaxId($vatTaxId);

    /**
     * @return string|null
     */
    public function getResellerId();

    /**
     * @param string|null $resellerId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setResellerId($resellerId);

    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string|null $comment
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setComment($comment);

    /**
     * @return string|null
     */
    public function getStreet();

    /**
     * @param string|null $street
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setStreet($street);

    /**
     * @return string|null
     */
    public function getCity();

    /**
     * @param string|null $city
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCity($city);

    /**
     * @return string|null
     */
    public function getCountryId();

    /**
     * @param string|null $countryId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCountryId($countryId);

    /**
     * @return string|null
     */
    public function getRegion();

    /**
     * @param string|null $region
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setRegion($region);

    /**
     * @return int|null
     */
    public function getRegionId();

    /**
     * @param int|null $regionId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setRegionId($regionId);

    /**
     * @return string|null
     */
    public function getPostcode();

    /**
     * @param string|null $postcode
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setPostcode($postcode);

    /**
     * @return string|null
     */
    public function getTelephone();

    /**
     * @param string|null $telephone
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setTelephone($telephone);

    /**
     * @return int|null
     */
    public function getCustomerGroupId();

    /**
     * @param int|null $customerGroupId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * @return int|null
     */
    public function getSalesRepresentativeId();

    /**
     * @param int|null $salesRepresentativeId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setSalesRepresentativeId($salesRepresentativeId);

    /**
     * @return int
     */
    public function getSuperUserId();

    /**
     * @param int|null $superUserId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setSuperUserId($superUserId);

    /**
     * @return string|null
     */
    public function getRejectReason();

    /**
     * @param string|null $reason
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setRejectReason($reason);

    /**
     * @return string|null
     */
    public function getRejectAt();

    /**
     * @param string|null $date
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setRejectAt($date);

    /**
     * @return \Amasty\CompanyAccount\Api\Data\CompanyExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Amasty\CompanyAccount\Api\Data\CompanyExtensionInterface $extensionAttributes
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setExtensionAttributes(
        \Amasty\CompanyAccount\Api\Data\CompanyExtensionInterface $extensionAttributes
    );

    /**
     * @return int[]
     */
    public function getCustomerIds();

    /**
     * @param int[] $customerIds
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function setCustomerIds(array $customerIds = []);

    /**
     * @param MagentoCustomerInterface $customer
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function addCustomer(MagentoCustomerInterface $customer);

    /**
     * @param MagentoCustomerInterface $customer
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function removeCustomer(MagentoCustomerInterface $customer);

    /**
     * @param int[] $customerIds
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function addCustomerIds(array $customerIds = []);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return bool
     */
    public function isRejected();

    /**
     * @return bool
     */
    public function isPending();

    /**
     * @return bool
     */
    public function hasRoles();

    /**
     * @param array|string $payments
     * @return CompanyInterface
     */
    public function setRestrictedPayments($payments);

    /**
     * @param bool $asArray
     * @return array|string
     */
    public function getRestrictedPayments($asArray = false);

    /**
     * @return bool
     */
    public function getUseCompanyGroup();

    /**
     * @param bool $flag
     * @return CompanyInterface
     */
    public function setUseCompanyGroup($flag = false);
}
