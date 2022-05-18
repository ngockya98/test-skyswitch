<?php

namespace Amasty\CompanyAccount\Controller\User;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class StatusChange extends AbstractUserAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::users_edit';

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Customer
     */
    private $companyCustomer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Amasty\CompanyAccount\Model\ResourceModel\Customer $companyCustomer
    ) {
        parent::__construct($context, $companyContext, $logger, $customerRepository);
        $this->companyCustomer = $companyCustomer;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/index');
        if (!$entityId) {
            return $resultRedirect;
        }

        try {
            $customer = $this->customerRepository->getById($entityId);
            if ($this->companyContext->isCustomerActive($customer)) {
                $this->companyCustomer->disableCustomers([$customer->getId()]);
            } else {
                $this->companyCustomer->enableCustomers([$customer->getId()]);
            }
            $this->messageManager->addSuccessMessage(__('The customer was updated successfully.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isActiveOrInactiveCompany()
            && parent::isAllowed();
    }
}
