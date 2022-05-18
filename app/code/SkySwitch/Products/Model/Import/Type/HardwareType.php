<?php

namespace SkySwitch\Products\Model\Import\Type;

use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;

class HardwareType extends AbstractType
{

    const BEFORE_OPTION_VALUE_DELIMITER = ';'; //phpcs:ignore
    const PAIR_VALUE_SEPARATOR = '='; //phpcs:ignore
    const VALUE_DYNAMIC = 'dynamic'; //phpcs:ignore
    const VALUE_FIXED = 'fixed'; //phpcs:ignore
    const NOT_FIXED_DYNAMIC_ATTRIBUTE = 'price_view'; //phpcs:ignore
    const SELECTION_PRICE_TYPE_FIXED = 0; //phpcs:ignore
    const SELECTION_PRICE_TYPE_PERCENT = 1; //phpcs:ignore

    /**
     * @var mixed
     */
    protected $connection;

    /**
     * @var mixed
     */
    protected $_resource;

    /**
     * @var array
     */
    protected $_cachedOptions = [];

    /**
     * @var array
     */
    protected $_cachedSkus = [];

    /**
     * @var array
     */
    protected $_cachedSkuToProducts = [];

    /**
     * @var array
     */
    protected $_cachedOptionSelectQuery = [];

    /**
     * Check row is valid
     *
     * @param array $rowData
     * @param mixed $rowNum
     * @param mixed $isNewProduct
     * @return mixed
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true) //phpcs:ignore
    {
        return parent::isRowValid($rowData, $rowNum, $isNewProduct);
    }
}
