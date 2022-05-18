<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Payment\Gateway\Command;

use Amasty\CompanyAccount\Model\Credit\Order\Place as PlaceOrder;
use Amasty\CompanyAccount\Model\CustomerDataProvider;
use LogicException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;

class SaleCommand implements CommandInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    public function __construct(
        ConfigInterface $configInterface,
        SubjectReader $subjectReader,
        CustomerDataProvider $customerDataProvider,
        PlaceOrder $placeOrder
    ) {
        $this->configInterface = $configInterface;
        $this->subjectReader = $subjectReader;
        $this->customerDataProvider = $customerDataProvider;
        $this->placeOrder = $placeOrder;
    }

    /**
     * @param array $commandSubject
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $commandSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDataObject->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new LogicException(__('Order Payment should be provided'));
        }

        $order = $payment->getOrder();
        $company = $this->customerDataProvider->getCompanyByCustomerId((int) $order->getCustomerId());

        if ($company === null) {
            throw new LocalizedException(__('Customer not assigned for company.'));
        }
        if ($this->configInterface->getValue('order_status') != Order::STATE_PROCESSING) {
            $order->setState(Order::STATE_NEW);
            $payment->setSkipOrderProcessing(true);
        }

        $payment->setAdditionalInformation('company_id', $company->getId());
        $payment->setAdditionalInformation('company_name', $company->getCompanyName());
        $this->placeOrder->execute($order);
    }
}
