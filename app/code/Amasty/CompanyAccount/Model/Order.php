<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\OrderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Order extends AbstractExtensibleModel implements OrderInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\CompanyAccount\Model\ResourceModel\Order::class);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->_getData(OrderInterface::COMPANY_ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(OrderInterface::COMPANY_ORDER_ID, $orderId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyId()
    {
        return $this->_getData(OrderInterface::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId($companyId)
    {
        $this->setData(OrderInterface::COMPANY_ID, $companyId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCompanyName()
    {
        return $this->_getData(OrderInterface::COMPANY_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyName($companyName)
    {
        $this->setData(OrderInterface::COMPANY_NAME, $companyName);

        return $this;
    }
}
