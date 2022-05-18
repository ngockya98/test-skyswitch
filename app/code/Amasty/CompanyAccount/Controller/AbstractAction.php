<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    public const RESOURCE = '';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Amasty\CompanyAccount\Model\CompanyContext
     */
    protected $companyContext;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->companyContext = $companyContext;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|null|Redirect
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->companyContext->getCurrentCustomerId() || !$this->isAllowed()) {
            $this->_actionFlag->set('', 'no-dispatch', true);

            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('noroute');
            return $resultRedirect;
        }

        return parent::dispatch($request);
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isResourceAllow(static::RESOURCE);
    }
}
