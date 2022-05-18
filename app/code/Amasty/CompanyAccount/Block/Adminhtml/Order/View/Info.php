<?php

namespace Amasty\CompanyAccount\Block\Adminhtml\Order\View;

use Amasty\CompanyAccount\Block\Adminhtml\AbstractInfo;

class Info extends AbstractInfo
{
    public const ENTITIES = [
        'current_invoice',
        'current_creditmemo',
        'current_shipment'
    ];

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        $companyAttributes = $order->getExtensionAttributes()->getAmCompanyAttributes();

        if ($companyAttributes) {
            $companyName = $companyAttributes->getCompanyName();
        } else {
            $companyName = $this->orderModel->getCompanyNameByOrderId($order->getId());
        }

        return $companyName;
    }

    /**
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getOrder()
    {
        if ($this->registry->registry('current_order')) {
            return $this->registry->registry('current_order');
        }
        if ($this->registry->registry('order')) {
            return $this->registry->registry('order');
        }

        foreach (self::ENTITIES as $entity) {
            if ($this->registry->registry($entity)) {
                return $this->registry->registry($entity)->getOrder();
            }
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }
}
