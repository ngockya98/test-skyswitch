<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Company;

use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend\Stdlib\Exception\LogicException;

class CustomerNameResolver
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerNameGenerationInterface $customerNameGeneration
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerNameGeneration = $customerNameGeneration;
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws LocalizedException
     */
    public function getCustomerById($customerId)
    {
        $customer = null;

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            throw new LogicException(__('Customer does not exist'));
        }

        return $customer;
    }

    /**
     * @param $customerId
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerName($customerId)
    {
        return $this->customerNameGeneration->getCustomerName($this->getCustomerById($customerId));
    }
}
