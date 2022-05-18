<?php
namespace SkySwitch\Orders\Api\Data;

interface TrackingInfoInterface
{
    /**
     * Get tracking info value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set tracking info value
     *
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value);
}
