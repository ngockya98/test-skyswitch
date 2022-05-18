<?php

namespace SkySwitch\Orders\Model;

use SkySwitch\Orders\Api\Data\ProvisionInterface;

class Provision implements ProvisionInterface
{
    /**
     * @var mixed
     */
    protected $provision;

    /**
     * Get provision value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->provision;
    }

    /**
     * Set provision value
     *
     * @param mixed $provision
     * @return mixed
     */
    public function setValue($provision)
    {
        return $this->provision = $provision;
    }
}
