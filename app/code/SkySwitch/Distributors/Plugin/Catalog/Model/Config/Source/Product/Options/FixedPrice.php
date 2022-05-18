<?php

namespace SkySwitch\Distributors\Plugin\Catalog\Model\Config\Source\Product\Options;

use Magento\Catalog\Model\Config\Source\Product\Options\Price;

class FixedPrice
{
    const VALUE = 'fixed_price'; //phpcs:ignore
    const LABEL = 'Fixed Markup'; //phpcs:ignore

    /**
     * After plugin for method toOptionArray
     *
     * Add new price option
     *
     * @param Price $subject
     * @param array $priceTypeOption
     * @return array
     */
    public function afterToOptionArray(Price $subject, array $priceTypeOption)
    {
        $priceTypeOption[] = ['value' => self::VALUE, 'label' => __(self::LABEL)];  //phpcs:ignore
        return $priceTypeOption;
    }
}
