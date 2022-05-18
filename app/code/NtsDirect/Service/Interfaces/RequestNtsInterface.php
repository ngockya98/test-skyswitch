<?php

namespace NtsDirect\Service\Interfaces;

interface RequestNtsInterface
{
    /**
     * @return \NtsDirect\Service\Interfaces\OrderInterface
     */
    public function getOrder();

    /**
     * @param \NtsDirect\Service\Interfaces\OrderInterface $value
     */
    public function setOrder(OrderInterface $value);
    
}
