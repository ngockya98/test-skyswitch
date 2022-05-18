<?php

namespace SkySwitch\Auth\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CompanyAttributesResource extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sky_auth_company_attributes', 'company_id');
    }
}
