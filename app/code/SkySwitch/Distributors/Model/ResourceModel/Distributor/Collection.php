<?php
namespace SkySwitch\Distributors\Model\ResourceModel\Distributor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'distributor_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'skyswitch_distributors_distributor_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'distributor_collection';

    /**
     * Define the resource model & the model.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        // @codingStandardsIgnoreStart
        $this->_init(
            'SkySwitch\Distributors\Model\Distributor',
            'SkySwitch\Distributors\Model\ResourceModel\Distributor'
        );
        // @codingStandardsIgnoreEnd
    }
}
