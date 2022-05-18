<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Payment\Gateway\Command;

use Amasty\CompanyAccount\Model\Credit\Order\Cancel as CancelOrder;
use Amasty\CompanyAccount\Model\CustomerDataProvider;
use LogicException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;

class CancelCommand implements CommandInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @var CancelOrder
     */
    private $cancelOrder;

    public function __construct(
        SubjectReader $subjectReader,
        CustomerDataProvider $customerDataProvider,
        CancelOrder $cancelOrder
    ) {
        $this->subjectReader = $subjectReader;
        $this->customerDataProvider = $customerDataProvider;
        $this->cancelOrder = $cancelOrder;
    }

    /**
     * @param array $commandSubject
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $commandSubject): void
    {
        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDataObject->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new LogicException(__('Order Payment should be provided'));
        }

        $order = $payment->getOrder();
        $company = $this->customerDataProvider->getCompanyByCustomerId((int) $order->getCustomerId());
        if ($company && $company->getCompanyId()) {
            $this->cancelOrder->execute($order);

            $amount = $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal());
            $message = __('Order is canceled. We reverted %1 to the company credit.', $amount);
        } else {
            $message = __(
                'Order is cancelled. The order amount is not reverted to the company credit '
                . 'because the company to which this customer belongs does not exist.'
            );
        }

        $order->addCommentToStatusHistory($message->render(), Order::STATE_CANCELED);
    }
}
