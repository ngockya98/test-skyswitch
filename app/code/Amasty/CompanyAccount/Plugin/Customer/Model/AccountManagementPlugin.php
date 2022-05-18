<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Customer\Model;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\ResourceModel\Customer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\State\UserLockedException;

class AccountManagementPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CompanyRepositoryInterface $companyRepository,
        CompanyContext $companyContext
    ) {
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
        $this->companyContext = $companyContext;
    }

    /**
     * @param AccountManagement $subject
     * @param callable $proceed
     * @param $username
     * @param $password
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAuthenticate(AccountManagement $subject, callable $proceed, $username, $password)
    {
        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if (!$this->companyContext->isCustomerActive($customer)) {
            if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getAmCompanyAttributes()) {
                $companyId = (int)$customer->getExtensionAttributes()->getAmCompanyAttributes()->getCompanyId();
                try {
                    $company = $this->companyRepository->getById($companyId);
                    if ($company->isRejected()) {
                        /**
                         * @TODO Should we add rejected_reason to the message?
                         */
                        throw new LocalizedException(__('The account is locked.'));
                    }
                } catch (NoSuchEntityException $e) {
                    throw new LocalizedException(__('The account is locked.'));
                }
            }

            throw new LocalizedException(__('The account is locked.'));
        }

        return $proceed($username, $password);
    }
}
