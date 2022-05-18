<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Sales\Controller\Order;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Order\Reorder;

class ReorderPlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(CompanyContext $companyContext, OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->companyContext = $companyContext;
    }

    /**
     * @param Reorder $subject
     * @param RequestInterface $request
     * @return void
     * @throws NotFoundException
     */
    public function beforeDispatch(Reorder $subject, RequestInterface $request)
    {
        $orderId = (int) $request->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        if ($order->getExtensionAttributes()->getAmcompanyAttributes()
            && $order->getExtensionAttributes()->getAmcompanyAttributes()->getCompanyId()
            && !$this->companyContext->isCurrentCustomer((int) $order->getCustomerId())
        ) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
