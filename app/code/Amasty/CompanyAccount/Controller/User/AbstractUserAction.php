<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\User;

use Magento\Customer\Api\CustomerRepositoryInterface;

abstract class AbstractUserAction extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        try {
            $customer = $this->customerRepository->getById($this->getRequest()->getParam('entity_id'));
            $isValidCustomer = $this->companyContext->isCurrentCompanyUser($customer);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isValidCustomer = false;
        }

        return $isValidCustomer && parent::isAllowed();
    }
}
