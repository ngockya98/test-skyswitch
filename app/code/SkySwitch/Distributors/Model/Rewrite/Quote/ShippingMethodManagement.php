<?php

namespace SkySwitch\Distributors\Model\Rewrite\Quote;

use Magento\Quote\Model\ShippingMethodManagement as MagentoShippingMethodManagement;
use Magento\Quote\Model\Quote;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Customer\Model\Session as CustomerSession;
use SkySwitch\Distributors\Managers\DistributorManager;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

class ShippingMethodManagement extends MagentoShippingMethodManagement
{

    /**
     * @var DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Get list of available shipping methods
     *
     * @param Quote $quote
     * @param ExtensibleDataInterface $address
     * @return ShippingMethodInterface[]
     */
    private function getShippingMethods(Quote $quote, $address)
    {
        $items_by_distributor = [];
        $items = $quote->getAllItems();
        $rates = [];
        $distributor_manager = ObjectManager::getInstance()->get(DistributorManager::class);
        $product_repo = ObjectManager::getInstance()->get(ProductRepository::class);

        $shipping_address = $quote->getShippingAddress();
        $shipping_address->addData($this->extractAddressData($address));
        $shipping_address->setCollectShippingRates(true);

        foreach ($items as $item) {
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

            $distributor_option = array_filter($options['options'], function ($option) {
                return $option['label'] === DistributorManager::OPTION_DISTRIBUTOR_TITLE;
            });

            $items_by_distributor[$distributor_option[0]['value']][] = [
                'qty' => $item->getQty(),
                'product_id' => $item->getProduct()->getId()
            ];
        }

        foreach ($items_by_distributor as $distributor_name => $order_items) {
            $products = [];
            array_map(function ($order_item) use (&$products, $product_repo) {
                $product = $product_repo->getById($order_item['product_id']);
                $products[] = $product->getSku();
            }, $order_items);

            $rates[] = [
                'distributor' => $distributor_name,
                'products' => implode(', ', $products),
                'rates' => empty($shipping_address->getData()) ? [] : $distributor_manager->getDistributorShippingRates(
                    $distributor_name,
                    $order_items,
                    $shipping_address->getData()
                )
            ];
        }

        return $rates;
    }

    /**
     * Get transform address interface into Array
     *
     * @param ExtensibleDataInterface $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof AddressInterface) {
            $className = AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }

        $addressData = $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
        unset($addressData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        return $addressData;
    }

    /**
     * Gets the data object processor
     *
     * @return DataObjectProcessor
     * @deprecated 101.0.0
     */
    private function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()
                ->get(DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }

    /**
     * @inheritDoc
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address);
    }

    /**
     * @inheritDoc
     */
    public function estimateByAddress($cartId, EstimateAddressInterface $address)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        return $this->getShippingMethods($quote, $address);
    }
}
