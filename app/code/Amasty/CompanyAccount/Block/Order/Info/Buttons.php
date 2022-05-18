<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Order\Info;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Order;

class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var Reorder
     */
    private $reorderHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(
        CompanyContext $companyContext,
        Reorder $reorderHelper,
        PostHelper $postHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $registry, $httpContext, $data);
        $this->reorderHelper = $reorderHelper;
        $this->postHelper = $postHelper;
        $this->companyContext = $companyContext;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function canReorder(Order $order): bool
    {
        return $this->companyContext->isCurrentCustomer((int) $order->getCustomerId())
            && $this->reorderHelper->canReorder($order->getEntityId());
    }

    /**
     * @param string $url
     * @return string
     */
    public function getPostData(string $url): string
    {
        return $this->postHelper->getPostData($url);
    }
}
