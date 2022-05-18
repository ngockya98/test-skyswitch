<?php

namespace NtsDirect\Service\Model\Api;

use NtsDirect\Service\Interfaces\OrderInterface;

class RequestNts
{
    protected $data;

    /**
     * @return \NtsDirect\Service\Interfaces\OrderInterface
     */
    public function getOrder()
    {
        return $this->data['Order'];
    }

    /**
     * @param \NtsDirect\Service\Interfaces\OrderInterface $value
     */
    public function setOrder(OrderInterface $value)
    {
        $this->data['Order'] = $value;
    }
    
}
