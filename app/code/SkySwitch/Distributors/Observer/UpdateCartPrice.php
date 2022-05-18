<?php

namespace SkySwitch\Distributors\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use SkySwitch\Distributors\Managers\DistributorManager;
use SkySwitch\Distributors\Model\DistributorFactory;

class UpdateCartPrice implements ObserverInterface
{
    /**
     * @var mixed
     */
    protected $distributor;

    /**
     * @param DistributorFactory $distributor_factory
     */
    public function __construct(DistributorFactory $distributor_factory)
    {
        $this->distributor = $distributor_factory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        $product = $item->getProduct();
        $options = $product->getTypeInstance(true)->getOrderOptions($product)['options'] ?? [];

        $price = null;

        foreach ($options as $option) {
            if ($option['label'] === DistributorManager::OPTION_DISTRIBUTOR_TITLE) {
                $this->distributor->load($option['value'], 'name');
                $price = $product->getData($this->distributor->getCode() . '_price');
                break;
            }
        }

        if ($price !== null) {
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
        }
    }
}
