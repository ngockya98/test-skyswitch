<?php
namespace SkySwitch\Distributors\Managers;

use Magento\Catalog\Model\ProductRepository;
use SkySwitch\Distributors\Model\ResourceModel\Data;
use SkySwitch\Distributors\Model\DistributorFactory;
use SkySwitch\Contracts\DistributorServiceFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Option;
use SkySwitch\Distributors\Model\Distributor;
use SkySwitch\Contracts\GetShippingRatesParams;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Psr\Log\LoggerInterface;
use SkySwitch\Contracts\CreateOrderParams;
use SkySwitch\Orders\Managers\OrderManager;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Backend\Customer\Interceptor;

class DistributorManager
{
    const OPTION_DISTRIBUTOR_TITLE = 'Distributor'; //phpcs:ignore
    const SKYSWITCH_DISTRIBUTOR = 'shipwire'; //phpcs:ignore
    const NTS_CODE = 'ntsdirect'; //phpcs:ignore
    const JENNE_CODE = 'jenne'; //phpcs:ignore

    /**
     * @var ProductRepository
     */
    protected $product_repo;

    /**
     * @var Data
     */
    protected $data_repository;

    /**
     * @var DistributorFactory
     */
    protected $distributor_factory;

    /**
     * @var DeploymentConfig
     */
    protected $deployment_config;

    /**
     * @var ObjectManager
     */
    protected $object_manager;

    /**
     * @var CheckoutSession
     */
    protected $checkout_session;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var phpcs:ignore
     */
    protected $session;

    /**
     * @var CollectionFactory
     */
    protected $product_collection_factory;

    /**
     * @var CustomerSession
     */
    protected $customer_session;

    /**
     * @var CredentialsManager
     */
    protected $credentials_manager;

    /**
     * @param ProductRepository $product_repo
     * @param Data $data_repository
     * @param DistributorFactory $distributor_factory
     * @param DeploymentConfig $deployment_config
     * @param Config $shipping_model_config
     * @param ScopeConfigInterface $scope_config
     * @param LoggerInterface $logger
     * @param OrderManager $order_manager
     * @param CheckoutSession $checkout_session
     * @param CollectionFactory $product_collection_factory
     * @param CustomerSession $customer_session
     * @param CustomerRepositoryInterface $customer_repository
     * @param CredentialsManager $credentials_manager
     */
    public function __construct(
        ProductRepository $product_repo,
        Data $data_repository,
        DistributorFactory $distributor_factory,
        DeploymentConfig $deployment_config,
        Config $shipping_model_config,
        ScopeConfigInterface $scope_config,
        LoggerInterface $logger,
        OrderManager $order_manager,
        CheckoutSession $checkout_session,
        CollectionFactory $product_collection_factory,
        CustomerSession $customer_session,
        CustomerRepositoryInterface $customer_repository,
        CredentialsManager $credentials_manager
    ) {
        $this->product_repo = $product_repo;
        $this->data_repository = $data_repository;
        $this->distributor_factory = $distributor_factory;
        $this->deployment_config = $deployment_config;
        $this->object_manager = ObjectManager::getInstance();
        $this->shipping_model_config = $shipping_model_config;
        $this->scope_config = $scope_config;
        $this->checkout_session = $checkout_session;
        $this->logger = $logger;
        $this->order_manager = $order_manager;
        $this->product_collection_factory = $product_collection_factory;
        $this->customer_session = $customer_session;
        $this->credentials_manager = $credentials_manager;

        if (!is_a($this->customer_session->getCustomer(), Interceptor::class)
            && !empty($this->customer_session->getCustomer()->getId())) {
            $this->customer = $customer_repository->getById($this->customer_session->getCustomer()->getId());
        }
    }

    /**
     * Process update stock price
     *
     * @return void
     */
    public function updateStockPrice()
    {
        $product_collection = $this->product_collection_factory->create();
        $product_collection->addAttributeToSelect('*');

        foreach ($product_collection as $product) {
            $this->logger->info('UPDATING PRODUCT STOCK: ' . $product->getName());
            if (empty($product->getData('distributors'))) {
                continue;
            }

            $this->saveDistributorInfo($this->product_repo->getById($product->getId()));
        }
    }

    /**
     * Update product stock price
     *
     * @param mixed $distributor
     * @param mixed $product
     * @return void
     */
    public function updateProductStockPrice($distributor, $product)
    {
        $service = DistributorServiceFactory::create(
            $distributor,
            $this->deployment_config->get('services/' . $distributor->getCode())
        );

        $response = $service->checkStock($product->getData($distributor->getCode() . '_sku'));

        if ($response->getPrice() != $product->getData($distributor->getCode() . '_price')) {
            $this->updateProduct($product, $response->getStock(), $response->getPrice(), $distributor);
        }
    }

    /**
     * Add new distributor custom option
     *
     * @param mixed $product
     * @param mixed $values
     * @return void
     */
    public function addDistributorCustomOption($product, $values)
    {
        $options = [
            [
                'sort_order' => 1,
                'title'         => self::OPTION_DISTRIBUTOR_TITLE,
                'price_type'    => 'fixed',
                'price'         => '',
                'type'          => 'radio',
                'is_require'    => 1,
                'values'        => $values
            ]
        ];

        if (!is_array($product->getOptions())) {
            return;
        }

        foreach ($product->getOptions() as $option) {
            if ($option->getTitle() === self::OPTION_DISTRIBUTOR_TITLE) {
                $option->delete();
            }
        }

        if (empty($values)) {
            $product->setHasOptions(0);
            $product->setCanSaveCustomOptions(false);
            return;
        }

        $product->setHasOptions(1);
        $product->setCanSaveCustomOptions(true);

        foreach ($options as $option) {
            $option = $this->object_manager->create(Option::class)
                ->setProductId($product->getId())
                ->setStoreId($product->getStoreId())
                ->addData($option);
            $option->save();
            $product->addOption($option);
        }
    }

    /**
     * Add new distributor custom option
     *
     * @param array|mixed $custom_values
     * @param int|string $distributor_id
     * @param mixed $product
     * @return void
     */
    public function addDistributorCustomOptionValues(&$custom_values, $distributor_id, $product)
    {
        $distributor = $this->distributor_factory->create();
        $distributor->load($distributor_id);
        $distributor_product =  $distributor->getDistributorProduct($product);
        $price = $product->getData($distributor->getCode() . '_price') ?? 0;

        if (empty($distributor_product)) {
            return;
        }

        if (empty($product->getData($distributor->getCode() . '_price'))) {
            return;
        }

        if ($distributor_product[0]['margin_type'] === Distributor::FIXED_MARGIN_TYPE) {
            $price = ($product->getData($distributor->getCode() . '_price') ?? 0)
                + $distributor_product[0]['margin_value'];
        }

        if ($distributor_product[0]['margin_type'] === Distributor::VALUE_MARGIN_TYPE) {
            $price = $distributor_product[0]['margin_value'];
        }

        if ($distributor_product[0]['margin_type'] === Distributor::PERCENT_MARGIN_TYPE) {
            if (($product->getData($distributor->getCode() . '_price') ?? 0) === 0) {
                $price = 0;
            } else {
                $price = ($product->getData($distributor->getCode() . '_price') ?? 0)
                    + ($distributor_product[0]['margin_value']
                        * 100
                        / ($product->getData($distributor->getCode() . '_price') ?? 0)
                    );
            }
        }

        $custom_values[] = [
            'record_id'=> $distributor_id,
            'title'=> $distributor->getName(),
            'price'=> $price ?? 0,
            'price_type'=>"fixed",
            'sort_order'=> $distributor_id,
            'is_delete'=> 0,
            'sku' => $product->getData($distributor->getCode() . '_sku') ?? ''
        ];
    }

    /**
     * Add new distributors
     *
     * @param mixed $product
     * @param int|string $distributor_id
     * @param array|mixed $select_wheres
     * @param array|mixed $select_bindings
     * @return void
     */
    public function addDistributors($product, $distributor_id, $select_wheres, $select_bindings)
    {
        $distributor = $this->distributor_factory->create();
        $distributor->load($distributor_id);

        $select_bindings['distributor_id'] = $distributor_id;
        $found = $this->data_repository->selectQuery('skyswitch_product_distributor', $select_wheres, $select_bindings);
        $option_values = [];

        if (!is_array($product->getOptions())) {
            return;
        }

        foreach ($product->getOptions() as $option) {
            if ($option->getTitle() == 'Distributor') {
                if (is_array($option['values'])) {
                    foreach ($option['values'] as $value) {
                        if ($value['title'] == $distributor->getName()) {
                            $option_values = $value;
                            break;
                        }
                    }
                    break;
                }
            }
        }

        if (empty($found)) {
            $data = [
                'product_id' => $product->getId(),
                'distributor_id' => $distributor_id,
                'margin_type' => $option_values['price_type'] ?? Distributor::FIXED_MARGIN_TYPE,
                'margin_value' => $option_values['price'] ?? 0,
            ];
            $this->data_repository->insert('skyswitch_product_distributor', $data);
            return;
        }

        $data = [
            'margin_type' => $option_values['price_type'] ?? Distributor::FIXED_MARGIN_TYPE,
            'margin_value' => $option_values['price'] ?? 0,
        ];

        $this->data_repository->update(
            'skyswitch_product_distributor',
            $data,
            ['product_id' => $product->getId(), 'distributor_id' => $distributor_id]
        );
    }

    /**
     * Return the cheapest price
     *
     * @param mixed $product
     * @param array|mixed $option_values
     * @return mixed
     */
    public function getCheapestPrice($product, $option_values = [])
    {
        $distributors = $product->getData('distributors');
        $distributors = $distributors === null
            ? []
            : (is_array($distributors) ? $distributors : explode(',', $distributors));
        $distributor = $this->distributor_factory->create();
        $cheapest = $product->getPrullice();

        if (!empty($option_values)) {
            foreach ($option_values as $index => $option_value) {
                if ($index == 0) {
                    $cheapest = $option_value['price'];
                    continue;
                }
                $cheapest = $cheapest < $option_value['price'] ? $cheapest : $option_value['price'];
            }

            return $cheapest;
        }

        if (!is_array($product->getOptions())) {
            return $cheapest;
        }

        foreach ($product->getOptions() as $option) {
            if ($option->getTitle() === self::OPTION_DISTRIBUTOR_TITLE) {
                foreach (array_values($option->getValues()) as $index => $option_value) {
                    if ($index == 0) {
                        $cheapest = $option_value->getPrice();
                        continue;
                    }

                    $cheapest = $cheapest < $option_value->getPrice() ? $cheapest : $option_value->getPrice();
                }
            }
        }

        return $cheapest;
    }

    /**
     * Update product
     *
     * @param mixed $product
     * @param mixed $stock
     * @param mixed $price
     * @param mixed $distributor
     * @return void
     */
    protected function updateProduct($product, $stock, $price, $distributor)
    {
        $set_method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $distributor->getCode() . '_stock')));
        $product->$set_method($stock ?? 0);
        $product->getResource()->saveAttribute($product, $distributor->getCode() . '_stock');

        $set_method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $distributor->getCode() . '_price')));
        $product->$set_method(round($price ?? 0, 2));
        $product->getResource()->saveAttribute($product, $distributor->getCode() . '_price');
    }

    /**
     * Return distributor shipping rates
     *
     * @param mixed $distributor_name
     * @param array|mixed $order_items
     * @param array $shipping_address
     * @return mixed
     */
    public function getDistributorShippingRates($distributor_name, $order_items, array $shipping_address)
    {
        $distributor = $this->distributor_factory->create();
        $distributor->load($distributor_name, 'name');

        $credentials = $this->credentials_manager->getCredentials(
            $distributor,
            $this->deployment_config,
            $this->customer
        );

        $service = DistributorServiceFactory::create($distributor, $credentials);

        $params = new GetShippingRatesParams();
        $state = $shipping_address['region']['region_code'] ?? $shipping_address['region'];

        $params->setShippingAddress(
            $shipping_address['street'],
            '',
            $shipping_address['city'],
            $state,
            $shipping_address['country_id'],
            $shipping_address['postcode'],
            $shipping_address['telephone'],
            empty($shipping_address['company']) ? 'residential' : 'comercial'
        );
        $params->setContact(
            $shipping_address['firstname'] . ' ' . $shipping_address['lastname'],
            $shipping_address['telephone']
        );
        $params->setPoNumber('SKY' . time());

        foreach ($order_items as $item) {
            $product = $this->product_repo->getById($item['product_id']);
            $params->addItem($product->getData($distributor->getCode() . '_sku'), (int)$item['qty']);
        }

        $rates = $service->getShippingRates($params)->getRates();
        $this->saveDistributorQuoteId($distributor->getCode(), $distributor_name, $rates);

        return $rates;
    }

    /**
     * Create new distributor orders
     *
     * @param mixed $orders
     * @return void
     */
    public function createDistributorOrders($orders)
    {
        $distributor = $this->distributor_factory->create();

        foreach ($orders as $order) {
            $items = $order->getAllItems();
            $options = $items[0]->getProductOptions();
            $distributor_option = array_filter($options['options'], function ($option) {
                return $option['label'] === self::OPTION_DISTRIBUTOR_TITLE;
            });
            $distributor->load($distributor_option[0]['value'], 'name');

            $shipping_address = $order->getShippingAddress()->getData();
            $shipping_address['region_code'] = $order->getShippingAddress()->getRegionCode();
            $billing_address = $order->getBillingAddress()->getData();
            $billing_address['region_code'] = $order->getBillingAddress()->getRegionCode();

            $response = $this->callCreateOrder($order, $shipping_address, $billing_address, $distributor);
            if ($response->isSuccessful()) {
                $this->logger->info($distributor_option[0]['value'].' order created with ID: '.$response->getOrderId());
                $order->setDistributorOrderNumber($response->getOrderId());
                $order->save();
            } else {
                $this->logger->error(
                    $distributor_option[0]['value'] . ' order unable to create. Error: ' . $response->getError()
                );
                $this->logger->debug('Response: ', $response->getRawResponse());
            }
        }
    }

    /**
     * Return list active shipping method
     *
     * @return array
     */
    public function getActiveShippingMethod()
    {
        $shippings = $this->shipping_model_config->getAllCarriers();
        $methods = [];

        foreach ($shippings as $shipping_code => $shipping_model) {
            if ($carrier_methods = $shipping_model->getAllowedMethods()) {
                foreach ($carrier_methods as $method_code => $method) {
                    $code = $shipping_code.'_'.$method_code;
                    $carrier_title = $this->scope_config->getValue('carriers/'. $shipping_code.'/title');
                    $methods[] = ['code'=> $code,'label'=> $carrier_title];
                }
            }
        }
        return $methods;
    }

    /**
     * Return order distributor name
     *
     * @param mixed $order
     * @return mixed|string
     */
    public static function getOrderDistributorName($order) //phpcs:ignore
    {
        if (empty($order->getAllItems())) {
            return 'No Distributor';
        }
        $options = $order->getAllItems()[0]->getProductOptions();

        $distributor_option = array_filter($options['options'], function ($option) {
            return $option['label'] === self::OPTION_DISTRIBUTOR_TITLE;
        });
        return $distributor_option[0]['value'] ?? 'Distributor';
    }

    /**
     * Return product distributor names
     *
     * @param mixed $product
     * @return array
     */
    public static function getProductDistributorNames($product) //phpcs:ignore
    {
        $object_manager = ObjectManager::getInstance();
        $distributor_factory = $object_manager->create(DistributorFactory::class);
        $distributor = $distributor_factory->create();
        $distributor_names = [];

        $distributor_ids = explode(',', $product->getDistributors());
        foreach ($distributor_ids as $distributor_id) {
            $distributor->load($distributor_id);
            $distributor_names[] = $distributor->getName();
        }

        return $distributor_names;
    }

    /**
     * Process save distributor info
     *
     * @param mixed $product
     * @return void
     */
    public function saveDistributorInfo($product)
    {
        $product_id = $product->getData('entity_id');
        $distributor = $this->distributor_factory->create();
        $distributors = $product->getData('distributors')===null ? [] : explode(',', $product->getData('distributors'));

        $custom_values = [];

        $select_wheres = [
            'skyswitch_product_distributor.product_id = :product_id',
            'skyswitch_product_distributor.distributor_id = :distributor_id'
        ];

        $this->data_repository->delete(
            'skyswitch_product_distributor',
            [
                ['condition' => 'product_id = ?', 'binding' => $product_id]
            ]
        );

        $select_bindings = ['product_id' => $product_id];

        foreach ($distributors as $distributor_id) {
            $this->addDistributors($product, $distributor_id, $select_wheres, $select_bindings);

            $distributor->load($distributor_id);
            $this->updateProductStockPrice($distributor, $product);

            $this->addDistributorCustomOptionValues($custom_values, $distributor_id, $product);
        }

        $this->addDistributorCustomOption($product, $custom_values);

        $product->setPrice($this->getCheapestPrice($product, $custom_values));
        $product->getResource()->saveAttribute($product, 'price');
    }

    /**
     * Process save distributor quote id
     *
     * @param string $distributor_code
     * @param string|mixed $distributor_name
     * @param mixed $rates
     * @return void
     */
    protected function saveDistributorQuoteId($distributor_code, $distributor_name, $rates)
    {
        switch ($distributor_code) {
            case self::NTS_CODE:
                $uns_method = OrderManager::UNSET_DISTRIBUTOR_QUOTE_SESSION_METHOD . $distributor_name;
                $set_method = OrderManager::SET_DISTRIBUTOR_QUOTE_SESSION_METHOD . $distributor_name;
                $this->checkout_session->$uns_method();
                $this->checkout_session->$set_method($rates[count($rates) - 1]['quote_id'] ?? '');
                break;

            case self::JENNE_CODE:
                foreach ($rates as $rate) {
                    $uns_method = OrderManager::UNSET_DISTRIBUTOR_QUOTE_SESSION_METHOD
                        . $distributor_name
                        . str_replace(' ', '', $rate['service_label']);
                    $set_method = OrderManager::SET_DISTRIBUTOR_QUOTE_SESSION_METHOD
                        . $distributor_name
                        . str_replace(' ', '', $rate['service_label']);
                    $this->checkout_session->$uns_method();
                    $this->checkout_session->$set_method($rate['quote_id']);
                    $this->logger->debug('QUOTE NUMBERS', $rate);
                }
                break;
        }
    }

    /**
     * Call create order function
     *
     * @param mixed $order
     * @param mixed $shipping_address
     * @param mixed $billing_address
     * @param mixed $distributor
     * @return mixed
     */
    private function callCreateOrder($order, $shipping_address, $billing_address, $distributor)
    {
        $session_method = OrderManager::GET_DISTRIBUTOR_SESSION_METHOD . $distributor->getName();
        $quote_method = OrderManager::GET_DISTRIBUTOR_QUOTE_SESSION_METHOD . $distributor->getName();
        if ($distributor->getCode() === self::JENNE_CODE) {
            $quote_method .= str_replace(' ', '', $this->checkout_session->$session_method()['service_label']);
        }
        $credentials = $this->credentials_manager->getCredentials(
            $distributor,
            $this->deployment_config,
            $this->customer
        );
        $service = DistributorServiceFactory::create($distributor, $credentials);

        $params = new CreateOrderParams();
        $params->setShippingAddress(
            $shipping_address['street'],
            '',
            $shipping_address['city'],
            $shipping_address['region_code'],
            $shipping_address['country_id'],
            $shipping_address['postcode'],
            $shipping_address['telephone']
        );
        $params->setBillingAddress(
            $billing_address['street'],
            '',
            $billing_address['city'],
            $billing_address['region_code'],
            $billing_address['country_id'],
            $billing_address['postcode'],
            $billing_address['telephone']
        );
        $params->setPoNumber($order->getCustomerId() . '-' . $order->getIncrementId());
        $params->setShippingCarrier($service->getCarrier($this->checkout_session->$session_method()['service_label']));
        $params->setShippingMethod($service->getMethod($this->checkout_session->$session_method()['service_label']));
        $params->setContact(
            $shipping_address['firstname'],
            $shipping_address['lastname'],
            $shipping_address['company'] ?? $shipping_address['firstname'] . ' ' . $shipping_address['lastname']
        );
        $params->setQuoteNumber($this->checkout_session->$quote_method() ?? '');

        $skus = [];
        foreach ($order->getAllItems() as $item) {
            if (!in_array($item->getProduct()->getData($distributor->getCode() . '_sku'), $skus)) {
                $params->addItem(
                    $item->getProduct()->getData($distributor->getCode() . '_sku'),
                    (int)$item->getQtyOrdered()
                );
                $skus[] = $item->getProduct()->getData($distributor->getCode() . '_sku');
            }
        }

        return $service->createOrder($params);
    }
}
