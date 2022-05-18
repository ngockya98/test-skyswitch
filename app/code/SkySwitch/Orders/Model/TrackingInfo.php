<?php

namespace SkySwitch\Orders\Model;

use SkySwitch\Orders\Api\Data\TrackingInfoInterface;

class TrackingInfo implements TrackingInfoInterface
{
    /**
     * @var mixed
     */
    protected $tracking_info;

    /**
     * Get tracking info value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->tracking_info;
    }

    /**
     * Set tracking info value
     *
     * @param mixed $tracking_info
     * @return mixed
     */
    public function setValue($tracking_info)
    {
        return $this->tracking_info = $tracking_info;
    }
}
