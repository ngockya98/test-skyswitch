<?php

namespace SkySwitch\Distributors\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use SkySwitch\Distributors\Managers\DistributorManager;
use SkySwitch\Distributors\Model\ResourceModel\Distributor\CollectionFactory as DistributorCollectionFactory;

class Attributes extends AbstractModifier
{
    /**
     * @var Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    /**
     * @var DistributorCollectionFactory
     */
    private $distributor_collection_factory;

    /**
     * @param ArrayManager $arrayManager
     * @param DistributorCollectionFactory $distributor_collection_factory
     */
    public function __construct(
        ArrayManager $arrayManager,
        DistributorCollectionFactory $distributor_collection_factory
    ) {
        $this->arrayManager = $arrayManager;
        $this->distributor_collection_factory = $distributor_collection_factory;
    }

    /**
     * ModifyData
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * ModifyMeta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $distributor_collection = $this->distributor_collection_factory->create();
        $distributors = $distributor_collection->getItems();

        foreach ($distributors as $distributor) {
            $attribute = $distributor->getCode() . '_price';
            $path = $this->arrayManager->findPath($attribute, $meta, null, 'children');
            $meta = $this->arrayManager->set(
                "{$path}/arguments/data/config/disabled",
                $meta,
                true
            );

            $attribute = $distributor->getCode() . '_stock';
            $path = $this->arrayManager->findPath($attribute, $meta, null, 'children');
            $meta = $this->arrayManager->set(
                "{$path}/arguments/data/config/disabled",
                $meta,
                true
            );
        }

        return $meta;
    }
}
