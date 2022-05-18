<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Company;

class RestrictedPayments implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var array
     */
    private $additionalPayments;

    public function __construct(
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        array $additionalPayments = []
    ) {
        $this->paymentMethodList = $paymentMethodList;
        $this->additionalPayments = $additionalPayments;
    }

    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        $options = [];
        $payments = $this->paymentMethodList->getList(null);
        foreach ($payments as $payment) {
            if ($payment->getTitle() && $payment->getCode()) {
                $options[] = [
                    'label' => $payment->getTitle() . ($payment->getIsActive() ? '' : ' ' . __('(disabled)')),
                    'value' => $payment->getCode()
                ];
            }
        }

        $options = $this->updateWithAdditional($options);

        return $options;
    }

    /**
     * @param array $options
     * @return array
     */
    private function updateWithAdditional(array $options): array
    {
        foreach ($this->additionalPayments as $additionalPayment) {
            $options[] = [
                'label' => $additionalPayment['label'],
                'value' => $additionalPayment['code']
            ];
        }

        return $options;
    }
}
