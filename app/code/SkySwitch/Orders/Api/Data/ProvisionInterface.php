<?php
namespace SkySwitch\Orders\Api\Data;

interface ProvisionInterface
{
    /**
     * Get provision value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set provision value
     *
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value);
}
