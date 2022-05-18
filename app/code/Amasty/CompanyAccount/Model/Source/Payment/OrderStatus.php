<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Payment;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderStatus implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $availableStatuses = ['pending', 'processing'];

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    public function __construct(OrderConfig $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->orderConfig->getStateStatuses([Order::STATE_NEW, Order::STATE_PROCESSING]);

        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        foreach ($statuses as $code => $label) {
            if (!in_array($code, $this->availableStatuses)) {
                continue;
            }
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
