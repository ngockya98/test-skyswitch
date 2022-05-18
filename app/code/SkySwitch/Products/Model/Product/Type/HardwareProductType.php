<?php

namespace SkySwitch\Products\Model\Product\Type;

use Magento\Catalog\Model\Product\Type\Simple;

class HardwareProductType extends Simple
{

    /**
     * Delete data specifically for new product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product) //phpcs:ignore
    {
        // method intentionally empty
    }
}
