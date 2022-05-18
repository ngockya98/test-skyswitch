<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Customer extends AbstractExtensibleModel implements CustomerInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\CompanyAccount\Model\ResourceModel\Customer::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(CustomerInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(CustomerInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyId()
    {
        return $this->_getData(CustomerInterface::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId($companyId)
    {
        $this->setData(CustomerInterface::COMPANY_ID, $companyId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getJobTitle()
    {
        return $this->_getData(CustomerInterface::JOB_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setJobTitle($jobTitle)
    {
        $this->setData(CustomerInterface::JOB_TITLE, $jobTitle);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(CustomerInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(CustomerInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTelephone()
    {
        return $this->_getData(CustomerInterface::TELEPHONE);
    }

    /**
     * @inheritdoc
     */
    public function setTelephone($telephone)
    {
        $this->setData(CustomerInterface::TELEPHONE, $telephone);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoleId()
    {
        return $this->_getData(CustomerInterface::ROLE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRoleId($roleId)
    {
        $this->setData(CustomerInterface::ROLE_ID, $roleId);

        return $this;
    }
}
