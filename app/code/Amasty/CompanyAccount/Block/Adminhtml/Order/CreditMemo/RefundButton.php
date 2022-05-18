<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Adminhtml\Order\CreditMemo;

use Amasty\CompanyAccount\Model\Payment\ConfigProvider as PaymentConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items;

class RefundButton extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_CompanyAccount::order/creditmemo/refund_button.phtml';

    /**
     * @return RefundButton
     */
    protected function _prepareLayout()
    {
        if ($this->isCompanyPaymentMethod()) {
            $this->addChild(
                'company_refund_button',
                Button::class,
                [
                    'label'   => __('Refund to Company Store Credit'),
                    'class'   => 'save submit-button primary',
                    'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOfflineToCompany()'
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isCompanyPaymentMethod()) {
            $result = parent::_toHtml();
        } else {
            $result = '';
        }

        return $result;
    }

    public function isCompanyPaymentMethod(): bool
    {
        /** @var Items $orderItemsBlock */
        $orderItemsBlock = $this->getLayout()->getBlock('order_items');
        return $orderItemsBlock->getOrder()->getPayment()->getMethod() === PaymentConfigProvider::METHOD_NAME;
    }
}
