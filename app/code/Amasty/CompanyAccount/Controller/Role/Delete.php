<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Role;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\RoleRepositoryInterface;
use Amasty\CompanyAccount\Model\ResourceModel\Customer\CollectionFactory;

class Delete extends AbstractRoleAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::roles_delete';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        RoleRepositoryInterface $roleRepository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $companyContext, $logger, $roleRepository);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $roleId = $this->getRequest()->getParam('role_id');
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/index');

        if (!$this->isValidData($roleId)) {
            return $resultRedirect;
        }

        try {
            $this->roleRepository->deleteById($roleId);
            $this->messageManager->addSuccessMessage(__('Role has been deleted successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addErrorMessage(
                __('An error occurred on the server. Please review the error log.')
            );
        }

        return $resultRedirect;
    }

    /**
     * @param int $roleId
     * @return bool
     */
    private function isValidData($roleId)
    {
        $isValid = true;
        if (!$roleId) {
            $isValid = false;
        }
        $customersCollection =  $this->collectionFactory->create()
            ->addFieldToFilter(CustomerInterface::ROLE_ID, $roleId);
        if ($customersCollection->getSize()) {
            $this->messageManager->addErrorMessage(__('You can\'t delete a Role which is assigned to Users. '
                . 'In order to proceed please assign associated Users to other Roles.'));
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
