<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Credit;

use Amasty\CompanyAccount\Controller\AbstractAction;
use Amasty\CompanyAccount\Model\Company\Role\Acl\IsAclShowed;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;

class Index extends AbstractAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::use_credit';

    /**
     * @var IsAclShowed
     */
    private $isAclShowed;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        IsAclShowed $isAclShowed,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->isAclShowed = $isAclShowed;
    }

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Company Store Credit'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function isAllowed(): bool
    {
        return $this->companyContext->isActiveOrInactiveCompany()
            && parent::isAllowed()
            && $this->isAclShowed->execute(self::RESOURCE);
    }
}
