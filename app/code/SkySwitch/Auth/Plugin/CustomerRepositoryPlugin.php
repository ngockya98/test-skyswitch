<?php

namespace SkySwitch\Auth\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use SkySwitch\Auth\Api\CustomerAttributeManagementInterface;
use SkySwitch\Auth\Api\Data\SkySwitchServiceInterface;
use SkySwitch\Auth\Model\Data\CustomerAttributes;

class CustomerRepositoryPlugin extends CustomerRepository
{
    /**
     * @var CustomerAttributeManagementInterface
     */
    private CustomerAttributeManagementInterface $customerAttributeManager;

    /**
     * @var SkySwitchServiceInterface
     */
    private SkySwitchServiceInterface $skySwitchService;

    /**
     * @var array
     */
    protected $attribute_cache = [];

    /**
     * @param CustomerAttributeManagementInterface $management
     * @param SkySwitchServiceInterface $skySwitchService
     */
    public function __construct(
        CustomerAttributeManagementInterface $management,
        SkySwitchServiceInterface            $skySwitchService
    ) {
        $this->customerAttributeManager = $management;
        $this->skySwitchService = $skySwitchService;
    }

    /**
     * Load extension attributes for customer after getById method
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGetById(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer
    ) {
        return $this->loadAttributes($customer);
    }

    /**
     * Save extension attributes for customer after save method
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param Customer $customer
     * @param mixed $result
     * @return Customer
     */
    public function afterSave(CustomerRepositoryInterface $customerRepository, Customer $customer, $result)
    {
        if ($result->getExtensionAttributes()) {
            $customer->setExtensionAttributes($result->getExtensionAttributes());
            $this->customerAttributeManager->saveCustomerAttributes($customer);
        }

        return $customer;
    }

    /**
     * Load extension attributes for customer after get method
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGet(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer
    ) {
        return $this->loadAttributes($customer);
    }

    /**
     * Load extension attributes for customer
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    private function loadAttributes(CustomerInterface $customer)
    {

        if ($extensionAttributes = $this->attribute_cache[$customer->getId()] ?? false) {
            $customer->setExtensionAttributes($extensionAttributes);

            return $customer;
        }

        $attributes = $this->customerAttributeManager->getByCustomerId($customer->getId());

        if (empty($attributes->getResellerId())) {
            return $customer;
        }

        $profile = $this->skySwitchService->getProfile($attributes->getResellerId());
        $settings = $this->skySwitchService->getSettings($profile->getAccountId());

        $extensionAttributes = $customer->getExtensionAttributes();
        $extensionAttributes->setFusionauthId($attributes->getFusionAuthId());
        $extensionAttributes->setResellerId($attributes->getResellerId());
        $extensionAttributes->setSkySwitchProfile($profile);
        $extensionAttributes->setSkySwitchSettings($settings);

        $this->attribute_cache[$customer->getId()] = $extensionAttributes;

        $customer->setExtensionAttributes($extensionAttributes);

        return $customer;
    }
}
