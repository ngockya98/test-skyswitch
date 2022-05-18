<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Credit;

use Magento\Framework\Data\OptionSourceInterface;

class OverdraftRepayType implements OptionSourceInterface
{
    public const DAY = 0;
    public const MONTH = 1;
    public const YEAR = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DAY,
                'label' => __('Day(s)')
            ],
            [
                'value' => self::MONTH,
                'label' => __('Month(s)')
            ],
            [
                'value' => self::YEAR,
                'label' => __('Year(s)')
            ]
        ];
    }

    public function toArray(): array
    {
        return [
            self::DAY => __('Day(s)'),
            self::MONTH => __('Month(s)'),
            self::YEAR => __('Year(s)')
        ];
    }
}
