<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Profile;

use Magento\Framework\App\ResponseInterface;

class Index extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::view_account';

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Company Account'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return (!$this->companyContext->isCurrentUserCompanyUser() && $this->companyContext->isAllowedCustomerGroup())
            || parent::isAllowed();
    }
}
