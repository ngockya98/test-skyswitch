<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCondition implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $additional = $observer->getEvent()->getAdditional();
        $conditions = $additional->getConditions() ?: [];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Company'),
                    'value' => \Amasty\CompanyAccount\Model\Condition\Company::class,
                ],
            ]
        );
        $additional->setConditions($conditions);
    }
}
