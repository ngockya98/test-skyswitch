<?php
namespace SkySwitch\Distributors\Plugin\Product;

use Magento\Catalog\Block\Product\ListProduct as MageListProduct;

class ListProduct
{
    /**
     * Before plugin for toHtml method
     *
     * Change template for ListProduct block
     *
     * @param MageListProduct $block
     * @return void
     */
    public function beforeToHtml(MageListProduct $block)
    {
        $block->setTemplate('SkySwitch_Distributors::product/list.phtml');
    }
}
