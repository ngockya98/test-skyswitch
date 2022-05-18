<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Controller\Adminhtml\Company\AbstractCompany;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete extends AbstractCompany
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $companyId = $this->getRequest()->getParam(CompanyInterface::COMPANY_ID);
        if (!$companyId) {
            $this->messageManager->addErrorMessage(__('We can\'t find account to delete.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $this->companyRepository->deleteById($companyId);
            $this->messageManager->addSuccessMessage(__('Company was successfully removed'));
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
