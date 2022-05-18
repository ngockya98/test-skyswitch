<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Sales\Api;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Order
     */
    private $orderResource;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Company
     */
    private $companyResource;

    /**
     * @var \Amasty\CompanyAccount\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Amasty\CompanyAccount\Api\Data\OrderInterfaceFactory
     */
    private $companyOrderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        DataObjectHelper $dataObjectHelper,
        \Amasty\CompanyAccount\Model\ResourceModel\Order $orderResource,
        \Amasty\CompanyAccount\Model\ResourceModel\Company $companyResource,
        \Amasty\CompanyAccount\Api\CompanyRepositoryInterface $companyRepository,
        \Amasty\CompanyAccount\Api\Data\OrderInterfaceFactory $companyOrderFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->orderResource = $orderResource;
        $this->companyResource = $companyResource;
        $this->companyRepository = $companyRepository;
        $this->companyOrderFactory = $companyOrderFactory;
        $this->logger = $logger;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $this->addCompanyAttributes($order);

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addCompanyAttributes(OrderInterface $order)
    {
        if ($order->getExtensionAttributes()
            && $order->getExtensionAttributes()->getAmCompanyAttributes()
        ) {
            return;
        }

        if (!$order->getExtensionAttributes()) {
            $orderExtension = $this->extensionFactory->create(OrderInterface::class);
            $order->setExtensionAttributes($orderExtension);
        }

        $companyAttributes = $this->getCompanyAttributes($order);

        if ($companyAttributes) {
            $order->getExtensionAttributes()->setData('amcompany_attributes', $companyAttributes);
        }
    }

    /**
     * @param OrderInterface $order
     * @return \Amasty\CompanyAccount\Api\Data\OrderInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCompanyAttributes(OrderInterface $order)
    {
        $companyAttributesArray = $this->getCompanyAttributesArray((int)$order->getId());
        if (!$companyAttributesArray) {
            return null;
        }
        $companyAttributes = $this->companyOrderFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $companyAttributes,
            $companyAttributesArray,
            \Amasty\CompanyAccount\Api\Data\OrderInterface::class
        );

        return $companyAttributes;
    }

    /**
     * @param int $orderId
     * @return array
     */
    private function getCompanyAttributesArray(int $orderId)
    {
        try {
            $companyAttributesArray = $this->orderResource->getOrderExtensionAttributes($orderId);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $companyAttributesArray = [];
        }

        return $companyAttributesArray;
    }
}
