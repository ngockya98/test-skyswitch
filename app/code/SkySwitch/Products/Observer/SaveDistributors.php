<?php

namespace SkySwitch\Products\Observer;

use Magento\Framework\Event\ObserverInterface;
use SkySwitch\Distributors\Model\ResourceModel\Data;
use SkySwitch\Distributors\Managers\DistributorManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use \SkySwitch\Distributors\Model\ResourceModel\Distributor\CollectionFactory as DistributorCollectionFactory;
use Psr\Log\LoggerInterface;

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
     * @var ProductRepositoryInterface
     */
    protected $product_repo;

    /**
     * @var DistributorCollectionFactory
     */
    protected $distributor_collection_factory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Data $data_repository
     * @param DistributorManager $distributor_manager
     * @param ProductRepositoryInterface $product_repo
     * @param DistributorCollectionFactory $distributor_collection_factory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $data_repository,
        DistributorManager $distributor_manager,
        ProductRepositoryInterface $product_repo,
        DistributorCollectionFactory $distributor_collection_factory,
        LoggerInterface $logger
    ) {
        $this->data_repository = $data_repository;
        $this->distributor_manager = $distributor_manager;
        $this->product_repo = $product_repo;
        $this->distributor_collection_factory = $distributor_collection_factory;
        $this->logger = $logger;
    }

    /**
     * Execute event method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $bunch = $observer->getBunch();

        foreach ($bunch as $product) {
            // $this->logger->debug('PROCESSING PRODUCT: ', $product['sku']);
            $product_obj = $this->product_repo->get($product['sku']);
            $distributor_names = explode('|', $product['distributors'] ?? '');
            foreach ($distributor_names as $distributor_name) {
                $distributor_collection = $this->distributor_collection_factory->create();
                $distributor_collection->addFieldToSelect('distributor_id')
                    ->addFieldToFilter('name', $distributor_name)
                    ->load();
                $distributor = $distributor_collection->getFirstItem();
                if ($distributor->getId()) {
                    $select_wheres = [
                        'skyswitch_product_distributor.product_id = :product_id',
                        'skyswitch_product_distributor.distributor_id = :distributor_id'
                    ];
                    $select_bindings = ['product_id' => $product_obj->getId(),
                        'distributor_id' => $distributor->getId()];
                    $this->distributor_manager->addDistributors(
                        $product_obj,
                        $distributor->getId(),
                        $select_wheres,
                        $select_bindings
                    );
                }
            }
        }
    }
}
