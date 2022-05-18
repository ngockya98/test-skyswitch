<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Customer;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const INACTIVE = 0;
    public const ACTIVE = 1;

    /**
     * @param int $value
     * @return string
     */
    public function getStatusLabelByValue(int $value)
    {
        $statuses = [
            self::INACTIVE => __('Inactive'),
            self::ACTIVE => __('Active')
        ];

        return isset($statuses[$value]) ? $statuses[$value] : '';
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ACTIVE,
                'label' => __('Active')
            ],
            [
                'value' => self::INACTIVE,
                'label' => __('Inactive')
            ]
        ];
    }
}
