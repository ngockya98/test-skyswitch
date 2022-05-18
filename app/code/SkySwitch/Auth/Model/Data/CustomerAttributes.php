<?php

namespace SkySwitch\Auth\Model\Data;

use Magento\Framework\Model\AbstractModel;
use SkySwitch\Auth\Api\Data\CustomerAttributesInterface;
use SkySwitch\Auth\Model\ResourceModel\CustomerAttributesResource;

class CustomerAttributes extends AbstractModel implements CustomerAttributesInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(CustomerAttributesResource::class);
    }

    /**
     * Return FusionAuth Id
     *
     * @return mixed
     */
    public function getFusionAuthId()
    {
        return $this->getData(self::FUSIONAUTH_ID);
    }

    /**
     * Set FusionAuth Id
     *
     * @param string $fusionAuthId
     * @return void
     */
    public function setFusionAuthId(string $fusionAuthId): void
    {
        $this->setData(self::FUSIONAUTH_ID, $fusionAuthId);
    }

    /**
     * Return Customer Id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Return Reseller Id
     *
     * @return mixed
     */
    public function getResellerId()
    {
        return $this->getData(self::RESELLER_ID);
    }

    /**
     * Set Reseller Id
     *
     * @param int|string $resellerId
     * @return void
     */
    public function setResellerId($resellerId)
    {
        $this->setData(self::RESELLER_ID, $resellerId);
    }
}
