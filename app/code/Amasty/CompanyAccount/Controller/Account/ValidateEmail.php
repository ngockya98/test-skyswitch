<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Account;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class ValidateEmail extends Action
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        CompanyContext $companyContext
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->companyContext = $companyContext;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $isCustomerExist = false;
        $isCustomerInCompany = false;

        $customer = $this->getCustomer();
        if ($customer) {
            $isCustomerExist = true;
            if ($this->companyContext->isUserCompanyUser($customer)) {
                $isCustomerInCompany = true;
            }
        }

        $resultJson->setData([
            'email_exist' => $isCustomerExist,
            'email_in_company' => $isCustomerInCompany,
        ]);

        return $resultJson;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomer()
    {
        $email = $this->getRequest()->getParam('email');
        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            $customer = null;
        }

        return $customer;
    }
}
