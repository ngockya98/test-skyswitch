<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Credit;

use Magento\Framework\Data\OptionSourceInterface;

class OverdraftRepay implements OptionSourceInterface
{
    public const UNLIMITED = 0;
    public const SET = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::UNLIMITED,
                'label' => __('Unlimited')
            ],
            [
                'value' => self::SET,
                'label' => __('Set')
            ]
        ];
    }
}
