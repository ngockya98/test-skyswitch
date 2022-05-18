<?php
namespace SkySwitch\Contracts;

use Magento\Framework\ObjectManagerInterface;
use SkySwitch\Distributors\Model\Distributor;

class DistributorServiceFactory
{
    /**
     * Create factory method
     *
     * @param Distributor $distributor
     * @param mixed $credentials
     * @return mixed
     */
    public static function create(Distributor $distributor, $credentials) //phpcs:ignore
    {
        $service_class = $distributor->getServiceClass();
        return new $service_class($credentials);
    }
}
