<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Payment\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;

class PaymentActionValueHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    public function __construct(
        ConfigInterface $configInterface,
        SubjectReader $subjectReader
    ) {
        $this->configInterface = $configInterface;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $subject
     * @param int|null $storeId
     * @return string
     */
    public function handle(array $subject, $storeId = null)
    {
        if ($this->configInterface->getValue('order_status', $storeId) != Order::STATE_PROCESSING) {
            $result = MethodInterface::ACTION_ORDER;
        } else {
            $result = $this->configInterface->getValue($this->subjectReader->readField($subject), $storeId);
        }

        return $result;
    }
}
