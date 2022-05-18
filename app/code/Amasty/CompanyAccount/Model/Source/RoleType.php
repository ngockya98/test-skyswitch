<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source;

class RoleType
{
    public const TYPE_ALL = 0;
    public const TYPE_DEFAULT_USER = 1;
    public const TYPE_DEFAULT_ADMINISTRATOR = 2;

    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            [
                'value' => self::TYPE_ALL,
                'label' => __('Default')
            ],
            [
                'value' => self::TYPE_DEFAULT_USER,
                'label' => __('Default User')
            ],
            [
                'value' => self::TYPE_DEFAULT_ADMINISTRATOR,
                'label' => __('Default Administrator')
            ]
        ];
    }
}
