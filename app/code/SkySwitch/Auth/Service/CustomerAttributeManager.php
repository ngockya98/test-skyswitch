<?php

namespace SkySwitch\Auth\Service;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use SkySwitch\Auth\Api\CustomerAttributeManagementInterface;
use SkySwitch\Auth\Api\Data\CustomerAttributesInterface;
use SkySwitch\Auth\Model\Data\CustomerAttributes;
use SkySwitch\Auth\Model\ResourceModel\CustomerAttributesResource;
use SkySwitch\Auth\Model\Data\CustomerAttributesFactory;

class CustomerAttributeManager implements CustomerAttributeManagementInterface
{
    /**
     * @var CustomerAttributesResource
     */
    protected CustomerAttributesResource $customerAttributesResource;

    /**
     * @var CustomerResourceModel
     */
    protected CustomerResourceModel $customerResource;

    /**
     * @var CustomerAttributesFactory
     */
    protected CustomerAttributesFactory $customerAttributesFactory;

    /**
     * @param CustomerAttributesResource $customerAttributesResource
     * @param CustomerResourceModel $customerResourceModel
     * @param CustomerAttributesFactory $customerAttributesFactory
     */
    public function __construct(
        CustomerAttributesResource $customerAttributesResource,
        CustomerResourceModel $customerResourceModel,
        CustomerAttributesFactory $customerAttributesFactory
    ) {
        $this->customerAttributesResource = $customerAttributesResource;
        $this->customerResource = $customerResourceModel;
        $this->customerAttributesFactory = $customerAttributesFactory;
    }

    /**
     * Return Customer Id form FusionAuth Id
     *
     * @param string $fusionauth_id
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdFromFusionAuthId(string $fusionauth_id)
    {
        return $this->customerAttributesResource->getCustomerIdByFusionAuthId($fusionauth_id);
    }

    /**
     * Return Customer attributes
     *
     * @param int $customerId
     * @return CustomerAttributesInterface
     */
    public function getByCustomerId(int $customerId)
    {
        $attributes = $this->getNewInstance();

        $this->customerAttributesResource->load($attributes, $customerId);

        return $attributes;
    }

    /**
     * Init new CustomerAttributesInterface
     *
     * @return CustomerAttributesInterface
     */
    public function getNewInstance(): CustomerAttributesInterface
    {
        return $this->customerAttributesFactory->create();
    }

    /**
     * Process save customer attributes
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function saveCustomerAttributes(CustomerInterface $customer)
    {
        $customerAttributes = $customer->getExtensionAttributes();
        $attributes = $this->getByCustomerId($customer->getId());

        if (!$attributes->getCustomerId()) {
            $this->customerAttributesResource
                ->getConnection()
                ->insert(CustomerAttributesResource::TABLE_NAME, [
                    CustomerAttributes::CUSTOMER_ID => $customer->getId(),
                    CustomerAttributes::FUSIONAUTH_ID => $customerAttributes->getFusionAuthId(),
                    CustomerAttributes::RESELLER_ID => $customerAttributes->getResellerId(),
                ]);
        } else {
            $attributes->setFusionAuthId($customerAttributes->getFusionAuthId());
            $attributes->setResellerId($customerAttributes->getResellerId());
            $this->customerAttributesResource->save($attributes);
        }
    }
}
