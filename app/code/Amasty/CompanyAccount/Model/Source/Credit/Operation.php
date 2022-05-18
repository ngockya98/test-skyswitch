<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Credit;

use Magento\Framework\Data\OptionSourceInterface;

class Operation implements OptionSourceInterface
{
    public const MINUS_BY_ADMIN = 'minus_admin';
    public const PLUS_BY_ADMIN = 'added_admin';
    public const PLUS_BY_COMPANY = 'repaid_company';
    public const OVERDRAFT_PENALTY = 'overdraft_penalty';
    public const PLACE_ORDER = 'place_order';
    public const PLACE_ORDER_OVERDRAFT = 'place_order_overdraft';
    public const CANCEL_ORDER = 'cancel_order';
    public const REFUND_ORDER = 'refund_order';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PLUS_BY_ADMIN,
                'label' => __('Added by Admin')
            ],
            [
                'value' => self::MINUS_BY_ADMIN,
                'label' => __('Subtracted by Admin')
            ],
            [
                'value' => self::PLUS_BY_COMPANY,
                'label' => __('Repaid by Company')
            ],
            [
                'value' => self::OVERDRAFT_PENALTY,
                'label' => __('Overdraft Penalty Applied')
            ],
            [
                'value' => self::PLACE_ORDER,
                'label' => __('Placed Order')
            ],
            [
                'value' => self::PLACE_ORDER_OVERDRAFT,
                'label' => __('Placed Order (Overdraft Used)')
            ],
            [
                'value' => self::CANCEL_ORDER,
                'label' => __('Canceled Order')
            ],
            [
                'value' => self::REFUND_ORDER,
                'label' => __('Refunded Order')
            ],
        ];
    }

    public function toArray(): array
    {
        return [
            self::PLUS_BY_ADMIN => __('Added by Admin'),
            self::MINUS_BY_ADMIN => __('Subtracted by Admin'),
            self::PLUS_BY_COMPANY => __('Repaid by Company'),
            self::OVERDRAFT_PENALTY => __('Overdraft Penalty Applied'),
            self::PLACE_ORDER => __('Placed Order'),
            self::PLACE_ORDER_OVERDRAFT => __('Placed Order (Overdraft Used)'),
            self::CANCEL_ORDER => __('Canceled Order'),
            self::REFUND_ORDER => __('Refunded Order')
        ];
    }
}
