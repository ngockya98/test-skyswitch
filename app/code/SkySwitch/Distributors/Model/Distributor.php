<?php

namespace SkySwitch\Distributors\Model;

use Magento\Framework\App\ObjectManager;
use SkySwitch\Distributors\Model\ResourceModel\Data;
use Magento\Framework\Model\AbstractModel;

class Distributor extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'skyswitch_distributors_distributor'; //phpcs:ignore
    const SERVICE_CLASS = 'service_class'; //phpcs:ignore
    const NAME = 'name'; //phpcs:ignore
    const ID = 'distributor_id'; //phpcs:ignore

    const FIXED_MARGIN_TYPE = 'fixed_price'; //phpcs:ignore
    const PERCENT_MARGIN_TYPE = 'percent'; //phpcs:ignore
    const VALUE_MARGIN_TYPE = 'fixed'; //phpcs:ignore
    const CODE = 'code'; //phpcs:ignore

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SkySwitch\Distributors\Model\ResourceModel\Distributor'); //phpcs:ignore
    }

    /**
     * Return a unique id for the model.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Service Class
     *
     * @return string|null
     */
    public function getServiceClass()
    {
        return $this->getData(self::SERVICE_CLASS);
    }

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Get Code
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Get Id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get Distributor Product
     *
     * @param mixed $product
     * @return array
     */
    public function getDistributorProduct($product)
    {
        $object_manager = ObjectManager::getInstance();
        $data_repository = $object_manager->create(Data::class);
        $select_wheres = [
            'skyswitch_product_distributor.product_id = :product_id',
            'skyswitch_product_distributor.distributor_id = :distributor_id'
        ];
        $select_bindings = ['product_id' => $product->getId(), 'distributor_id' => $this->getId()];

        return $data_repository->selectQuery('skyswitch_product_distributor', $select_wheres, $select_bindings);
    }
}
