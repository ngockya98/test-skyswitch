<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Payment\Gateway\Command;

use Amasty\CompanyAccount\Model\Backend\Gateway\IsRefundToCompany;
use Amasty\CompanyAccount\Model\Credit\Order\Refund as RefundOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class RefundCommand implements CommandInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var RefundOrder
     */
    private $refundOrder;

    /**
     * @var IsRefundToCompany
     */
    private $isRefundToCompany;

    public function __construct(
        SubjectReader $subjectReader,
        RefundOrder $refundOrder,
        IsRefundToCompany $isRefundToCompany
    ) {
        $this->subjectReader = $subjectReader;
        $this->refundOrder = $refundOrder;
        $this->isRefundToCompany = $isRefundToCompany;
    }

    /**
     * @param array $commandSubject
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(array $commandSubject): void
    {
        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDataObject->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException(__('Order Payment should be provided'));
        }

        $amount = $payment->getOrder()->getBaseCurrency()->formatTxt($payment->getCreditmemo()->getBaseGrandTotal());
        if ($this->isRefundToCompany->execute()) {
            $this->refundOrder->execute($payment->getOrder(), $payment->getCreditmemo());
            $message = __('%1 was refunded to the company store credit.', $amount);
        } else {
            $message = __('%1 was refunded offline.', $amount);
        }

        $payment->setMessage($message);
    }
}
