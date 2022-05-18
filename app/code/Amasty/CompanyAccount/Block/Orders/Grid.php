<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Orders;

use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Amasty\CompanyAccount\Model\ResourceModel\Order\CollectionFactory as CompanyOrderCollectionFactory;
use Magento\Sales\Helper\Reorder;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Customer\Model\Session as CustomerSession;

class Grid extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_CompanyAccount::company/orders/grid.phtml';

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var Collection
     */
    private $companyOrders;

    /**
     * @var Reorder
     */
    private $reorder;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @var CompanyOrderCollectionFactory
     */
    private $companyOrderCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CustomerSession $customerSession,
        OrderConfig $orderConfig,
        CompanyContext $companyContext,
        Reorder $reorder,
        PostHelper $postHelper,
        CompanyOrderCollectionFactory $companyOrderCollectionFactory,
        JoinProcessorInterface $joinProcessor,
        array $data = []
    ) {
        parent::__construct($context, $collectionFactory, $customerSession, $orderConfig, $data);
        $this->companyContext = $companyContext;
        $this->reorder = $reorder;
        $this->postHelper = $postHelper;
        $this->companyOrderCollectionFactory = $companyOrderCollectionFactory;
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * @return Collection|bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!$this->companyOrders) {
            $company = $this->companyContext->getCurrentCompany();
            $this->companyOrders = $this->companyOrderCollectionFactory
                ->create()
                ->getCompanyOrders($company->getCompanyId());
            $this->joinProcessor->process($this->companyOrders);
        }

        return $this->companyOrders;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isCanReorder(\Magento\Sales\Model\Order $order): bool
    {
        return $this->companyContext->isCurrentCustomer((int) $order->getCustomerId())
            && $this->reorder->canReorder($order->getEntityId());
    }

    /**
     * @param string $url
     * @return string
     */
    public function getPostData(string $url): string
    {
        return $this->postHelper->getPostData($url);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getExtraChildHtml(\Magento\Sales\Model\Order $order): string
    {
        $extra = $this->getChildBlock('extra.container');
        if ($extra) {
            $extra->setOrder($order);
            $html = $extra->getChildHtml();
        }

        return $html ?? '';
    }

    public function getCustomerName(\Magento\Sales\Model\Order $order): string
    {
        return $this->getOrderInfo()->getCustomerName($order);
    }

    private function getOrderInfo(): \Amasty\CompanyAccount\ViewModel\Order
    {
        return $this->getData('orderInfo');
    }
}
