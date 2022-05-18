<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Profile;

use Magento\Framework\App\ResponseInterface;

class Edit extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::edit_account';

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Edit Company Account'));

        return $resultPage;
    }
}
