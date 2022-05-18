<?php

namespace SkySwitch\Distributors\Observer;

use Magento\Framework\Event\ObserverInterface;
use SkySwitch\Distributors\Model\ResourceModel\Data;
use SkySwitch\Distributors\Managers\DistributorManager;

class SaveDistributors implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $data_repository;

    /**
     * @var DistributorManager
     */
    protected $distributor_manager;

    /**
     * @param Data $data_repository
     * @param DistributorManager $distributor_manager
     */
    public function __construct(
        Data $data_repository,
        DistributorManager $distributor_manager
    ) {
        $this->data_repository = $data_repository;
        $this->distributor_manager = $distributor_manager;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->distributor_manager->saveDistributorInfo($observer->getProduct());
    }
}
