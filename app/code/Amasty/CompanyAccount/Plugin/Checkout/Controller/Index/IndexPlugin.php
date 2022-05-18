<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Checkout\Controller\Index;

class IndexPlugin
{
    public const RESOURCE = 'Amasty_CompanyAccount::orders_add';

    /**
     * @var \Amasty\CompanyAccount\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    public function __construct(
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->companyContext = $companyContext;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $redirectFactory;
    }

    /**
     * @param mixed $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute($subject, callable $proceed)
    {
        if ($this->companyContext->getCurrentCustomerId()) {
            $company = $this->companyContext->getCurrentCompany();
            if (($company->getCompanyId() && !$company->isActive())
                || !$this->companyContext->isResourceAllow(self::RESOURCE)
            ) {
                $this->messageManager->addErrorMessage(__('You do not have permission to proceed the checkout.'));
                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }
        }

        return $proceed();
    }
}
