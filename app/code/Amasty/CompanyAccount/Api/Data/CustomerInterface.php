<?php

namespace Amasty\CompanyAccount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CustomerInterface extends ExtensibleDataInterface
{
    public const TABLE_NAME = 'amasty_company_account_customer';
    public const CUSTOMER_ID = 'customer_id';
    public const COMPANY_ID = 'company_id';
    public const JOB_TITLE = 'job_title';
    public const STATUS = 'status';
    public const TELEPHONE = 'telephone';
    public const ROLE_ID = 'role_id';

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getCompanyId();

    /**
     * @param int $companyId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface
     */
    public function setCompanyId($companyId);

    /**
     * @return string|null
     */
    public function getJobTitle();

    /**
     * @param string|null $jobTitle
     *
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface
     */
    public function setJobTitle($jobTitle);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getTelephone();

    /**
     * @param string|null $telephone
     *
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface
     */
    public function setTelephone($telephone);

    /**
     * @return int|null
     */
    public function getRoleId();

    /**
     * @param int|null $roleId
     *
     * @return \Amasty\CompanyAccount\Api\Data\CustomerInterface
     */
    public function setRoleId($roleId);
}
