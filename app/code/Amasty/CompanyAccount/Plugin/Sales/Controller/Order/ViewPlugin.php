<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Sales\Controller\Order;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Controller\Order\View;

class ViewPlugin
{
    public const RESOURCE = 'Amasty_CompanyAccount::orders_view';

    /**
     * @var \Amasty\CompanyAccount\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    public function __construct(
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->companyContext = $companyContext;
        $this->resultRedirectFactory = $redirectFactory;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundDispatch(View $subject, callable $proceed, RequestInterface $request)
    {
        if ($this->companyContext->getCurrentCustomerId()
            && !$this->companyContext->isResourceAllow(self::RESOURCE)
        ) {
            return $this->resultRedirectFactory->create()->setPath('noroute');
        }

        return $proceed($request);
    }
}
