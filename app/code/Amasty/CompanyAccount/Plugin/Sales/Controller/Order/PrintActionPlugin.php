<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Sales\Controller\Order;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Controller\Order\PrintAction;

class PrintActionPlugin
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(CompanyContext $companyContext)
    {
        $this->companyContext = $companyContext;
    }

    /**
     * @param PrintAction $subject
     * @param RequestInterface $request
     * @return void
     * @throws NotFoundException
     */
    public function beforeDispatch(PrintAction $subject, RequestInterface $request)
    {
        if ($this->companyContext->getCurrentCustomerId()
            && !$this->companyContext->isResourceAllow(ViewPlugin::RESOURCE)
        ) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
