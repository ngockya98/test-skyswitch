<?php

namespace Amasty\CompanyAccount\Controller\User;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

class Delete extends AbstractUserAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::users_delete';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $companyContext, $logger, $customerRepository);
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/index');

        if (!$this->isValidData($entityId)) {
            return $resultRedirect;
        }

        try {
            $this->registry->register('isSecureArea', true);
            $this->customerRepository->deleteById($entityId);
            $this->registry->unregister('isSecureArea');
            $this->messageManager->addSuccessMessage(__('The customer was deleted successfully.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }

    /**
     * @param int $entityId
     * @return bool
     */
    private function isValidData($entityId)
    {
        $isValid = true;
        if (!$entityId) {
            $isValid = false;
        }
        if ($entityId == $this->companyContext->getCurrentCustomerId()) {
            $this->messageManager->addErrorMessage(__('You can’t delete yourself. The action was not completed.'));
            $isValid = false;
        }

        return $isValid;
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
