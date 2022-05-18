<?php
namespace SkySwitch\Distributors\Block\Product;

use Magento\Catalog\Block\Product\View as MagentoView;
use SkySwitch\Distributors\Model\DistributorFactory;
use Magento\Framework\App\ObjectManager;
use SkySwitch\Distributors\Managers\DistributorManager;

class View extends MagentoView
{
    /**
     * Return distributor stock
     *
     * @param mixed $product
     * @param int|string $distributor_id
     * @return int
     */
    public function getDistributorStock($product, $distributor_id)
    {
        $object_manager = ObjectManager::getInstance();
        $distributor_factory = $object_manager->create(DistributorFactory::class);
        $distributor = $distributor_factory->create();
        $distributor->load($distributor_id);
        $stock = $product->getData($distributor->getCode() . '_stock');

        return empty($stock) ? 0 : $stock ;
    }

    /**
     * Return distributor name
     *
     * @param int|string $distributor_id
     * @return mixed
     */
    public function getDistributorName($distributor_id)
    {
        $object_manager = ObjectManager::getInstance();
        $distributor_factory = $object_manager->create(DistributorFactory::class);
        $distributor = $distributor_factory->create();
        $distributor->load($distributor_id);

        return $distributor->getName();
    }
}
