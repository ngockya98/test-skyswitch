<?php
namespace SkySwitch\Distributors\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use \SkySwitch\Distributors\Model\ResourceModel\Distributor\Collection as DistributorCollection;
use \SkySwitch\Distributors\Model\ResourceModel\Distributor\CollectionFactory as DistributorCollectionFactory;

class ExtensionOption extends AbstractSource
{
    /**
     * @var phpcs:ignore
     */
    protected $optionFactory;

    /**
     * @var DistributorCollectionFactory
     */
    protected $distributor_collection_factory;

    /**
     * @param DistributorCollectionFactory $distributor_collection_factory
     */
    public function __construct(DistributorCollectionFactory $distributor_collection_factory)
    {
        $this->distributor_collection_factory = $distributor_collection_factory;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [];

        $distributor_collection = $this->distributor_collection_factory->create();
        $distributor_collection->addFieldToSelect('*')->load();
        $distributors = $distributor_collection->getItems();

        foreach ($distributors as $distributor) {
            $this->_options[] = ['label' => $distributor->getName(), 'value' => $distributor->getDistributorId()];
        }

        return $this->_options;
    }
}
