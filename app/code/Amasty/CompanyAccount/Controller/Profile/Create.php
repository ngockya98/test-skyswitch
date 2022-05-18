<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Profile;

class Create extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::edit_account';

    /**
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('New Company Account'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isAllowedCustomerGroup()
            && !$this->companyContext->isCurrentUserCompanyUser();
    }
}
