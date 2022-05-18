<?php

namespace SkySwitch\Auth\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use SkySwitch\Auth\Api\Data\CustomerAttributesInterface;
use SkySwitch\Auth\Model\FusionAuthProfile;

interface CustomerAttributeManagementInterface
{
    /**
     * Get Customer by Id
     *
     * @param int $customerId
     * @return mixed
     */
    public function getByCustomerId(int $customerId);

    /**
     * Get Customer Id using FusionAuth Id
     *
     * @param string $fusionAuthId
     * @return mixed
     */
    public function getCustomerIdFromFusionAuthId(string $fusionAuthId);

    /**
     * Save extension attributes for customer
     *
     * @param CustomerInterface $customerAttributes
     * @return mixed
     */
    public function saveCustomerAttributes(CustomerInterface $customerAttributes);
}
