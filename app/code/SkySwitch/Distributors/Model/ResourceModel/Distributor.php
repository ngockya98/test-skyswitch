<?php
namespace SkySwitch\Distributors\Model\ResourceModel;

class Distributor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('skyswitch_distributors', 'distributor_id');
    }
}
