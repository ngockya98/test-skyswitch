<?php
namespace SkySwitch\Orders\Managers;

use SkySwitch\Distributors\Managers\DistributorManager;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;
use SkySwitch\Distributors\Model\DistributorFactory;
use SkySwitch\Contracts\DistributorServiceFactory;
use Magento\Framework\App\DeploymentConfig;
use SkySwitch\Distributors\Managers\CredentialsManager;
use SkySwitch\Orders\Model\TrackingInfoFactory;
use SkySwitch\Orders\Model\ProvisionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Backend\Customer\Interceptor;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use SkySwitch\Contracts\Mac;
use SkySwitch\Contracts\TrackingInfo;
use SkySwitch\Distributors\Model\ResourceModel\Data;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\File\Csv;

class OrderManager
{
    const GET_DISTRIBUTOR_SESSION_METHOD = 'getShippingPrice'; //phpcs:ignore
    const SET_DISTRIBUTOR_SESSION_METHOD = 'setShippingPrice'; //phpcs:ignore
    const UNSET_DISTRIBUTOR_SESSION_METHOD = 'unsShippingPrice'; //phpcs:ignore

    const GET_DISTRIBUTOR_QUOTE_SESSION_METHOD = 'getQuote'; //phpcs:ignore
    const SET_DISTRIBUTOR_QUOTE_SESSION_METHOD = 'setQuote'; //phpcs:ignore
    const UNSET_DISTRIBUTOR_QUOTE_SESSION_METHOD = 'unsQuote'; //phpcs:ignore

    const ORDERS_TRACKING_TABLE = 'skyswitch_orders_tracking'; //phpcs:ignore
    const ORDERS_PROVISIONING_TABLE = 'skyswitch_orders_macs'; //phpcs:ignore

    const MACS_CSV_FILE_PATH = '/import/csv/'; //phpcs:ignore

    /**
     * @var OrderInterfaceFactory
     */
    protected $order_factory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $order_repository;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $order_item_repository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $price_currency;

    /**
     * @var Session
     */
    protected $checkout_session;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DistributorFactory
     */
    protected $distributor_factory;

    /**
     * @var DeploymentConfig
     */
    protected $deployment_config;

    /**
     * @var TrackingInfoFactory
     */
    protected $tracking_info_factory;

    /**
     * @var ProvisionFactory
     */
    protected $provision_info_factory;

    /**
     * @var CredentialsManager
     */
    protected $credentials_manager;

    /**
     * @var CustomerSession
     */
    protected $customer_session;

    /**
     * @var mixed
     */
    protected $customer;

    /**
     * @var Data
     */
    protected $data_repository;

    /**
     * @var DirectoryList
     */
    protected $dir;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @param OrderInterfaceFactory $order_factory
     * @param OrderRepositoryInterface $order_repository
     * @param OrderItemRepositoryInterface $order_item_repository
     * @param PriceCurrencyInterface $price_currency
     * @param Session $checkout_session
     * @param LoggerInterface $logger
     * @param DistributorFactory $distributor_factory
     * @param DeploymentConfig $deployment_config
     * @param TrackingInfoFactory $tracking_info_factory
     * @param ProvisionFactory $provision_info_factory
     * @param CredentialsManager $credentials_manager
     * @param CustomerSession $customer_session
     * @param CustomerRepositoryInterface $customer_repository
     * @param Data $data_repository
     * @param DirectoryList $dir
     * @param File $file
     * @param Csv $csv
     */
    public function __construct(
        OrderInterfaceFactory $order_factory,
        OrderRepositoryInterface $order_repository,
        OrderItemRepositoryInterface $order_item_repository,
        PriceCurrencyInterface $price_currency,
        Session $checkout_session,
        LoggerInterface $logger,
        DistributorFactory $distributor_factory,
        DeploymentConfig $deployment_config,
        TrackingInfoFactory $tracking_info_factory,
        ProvisionFactory $provision_info_factory,
        CredentialsManager $credentials_manager,
        CustomerSession $customer_session,
        CustomerRepositoryInterface $customer_repository,
        Data $data_repository,
        DirectoryList $dir,
        File $file,
        Csv $csv
    ) {
        $this->order_factory = $order_factory;
        $this->order_repository = $order_repository;
        $this->order_item_repository = $order_item_repository;
        $this->price_currency = $price_currency;
        $this->checkout_session = $checkout_session;
        $this->logger = $logger;
        $this->distributor_factory = $distributor_factory;
        $this->deployment_config = $deployment_config;
        $this->tracking_info_factory = $tracking_info_factory;
        $this->provision_info_factory = $provision_info_factory;
        $this->credentials_manager = $credentials_manager;
        $this->customer_session = $customer_session;
        $this->data_repository = $data_repository;
        $this->dir = $dir;
        $this->file = $file;
        $this->csv = $csv;

        if (!is_a($this->customer_session->getCustomer(), Interceptor::class)
            && !empty($this->customer_session->getCustomer()->getId())
        ) {
            $this->customer = $customer_repository->getById($this->customer_session->getCustomer()->getId());
        }
    }

    /**
     * Get required data of order
     *
     * @param mixed $data
     * @return mixed
     */
    private function getRequiredDataOfOrder($data)
    {
        $unset_keys = ['entity_id', 'parent_id', 'item_id', 'order_id'];
        foreach ($unset_keys as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }

        $unset_keys = ['increment_id', 'items', 'addresses', 'payment'];
        foreach ($unset_keys as $key) {
            if (isset($data[$key])) {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * Set required data of order
     *
     * @param mixed $order_items
     * @param mixed $order
     * @param int|float $base_shipping_amount
     * @param int|float $tax
     * @return mixed
     */
    private function setRequiredDataOfOrder($order_items, $order, $base_shipping_amount, $tax = 0)
    {
        $total_qty = 0;
        $sub_total = 0;
        $base_sub_total = 0;
        $sub_total_incl_tax = 0;
        $base_sub_total_incl_tax = 0;
        $discount = 0;
        $base_discount = 0;

        $tax = $this->price_currency->convert($this->price_currency->round($tax));
        $base_tax = $tax;

        foreach ($order_items as $order_item) {
            if ($order_item->getParentItemId()) {
                $parent_order_item = $this->order_item_repository->get($order_item->getParentItemId());
                $total_qty += $parent_order_item->getQtyOrdered();
                $sub_total += $this->price_currency->round(
                    $parent_order_item->getQtyOrdered() * $parent_order_item->getPrice()
                );
                $base_sub_total += $this->price_currency->round(
                    $parent_order_item->getQtyOrdered() * $parent_order_item->getBasePrice()
                );
                $sub_total_incl_tax += $this->price_currency->round(
                    $parent_order_item->getQtyOrdered() * $parent_order_item->getPriceInclTax()
                );
                $base_sub_total_incl_tax += $this->price_currency->round(
                    $parent_order_item->getQtyOrdered() * $parent_order_item->getBasePriceInclTax()
                );
                if ($parent_order_item->getDiscountPercent()) {
                    $discount += $this->price_currency->round(
                        $sub_total * ($parent_order_item->getDiscountPercent() / 100)
                    );
                    $base_discount += $this->price_currency->round(
                        $base_sub_total * ($parent_order_item->getDiscountPercent() / 100)
                    );
                }
                if ($parent_order_item->getTaxPercent()) {
                    $tax += $this->price_currency->round(
                        $sub_total * ($parent_order_item->getTaxPercent() / 100)
                    );
                    $base_tax += $this->price_currency->round(
                        $base_sub_total * ($parent_order_item->getTaxPercent() / 100)
                    );
                }
            } else {
                if ($order_item->getPrice() > 0) {
                    $total_qty += $order_item->getQtyOrdered();
                    $sub_total += $this->price_currency->round(
                        $order_item->getQtyOrdered() * $order_item->getPrice()
                    );
                    $base_sub_total += $this->price_currency->round(
                        $order_item->getQtyOrdered() * $order_item->getBasePrice()
                    );
                    $sub_total_incl_tax += $this->price_currency->round(
                        $order_item->getQtyOrdered() * $order_item->getPriceInclTax()
                    );
                    $base_sub_total_incl_tax += $this->price_currency->round(
                        $order_item->getQtyOrdered() * $order_item->getBasePriceInclTax()
                    );
                    if ($order_item->getDiscountPercent()) {
                        $discount += $this->price_currency->round(
                            $sub_total * ($order_item->getDiscountPercent() / 100)
                        );
                        $base_discount += $this->price_currency->round(
                            $base_sub_total * ($order_item->getDiscountPercent() / 100)
                        );
                    }
                    if ($order_item->getTaxPercent()) {
                        $tax += $this->price_currency->round(
                            $sub_total * ($order_item->getTaxPercent() / 100)
                        );
                        $base_tax += $this->price_currency->round(
                            $base_sub_total * ($order_item->getTaxPercent() / 100)
                        );
                    }
                }
            }
        }
        $amount_discount = $discount;
        $base_amount_discount = $base_discount;
        if ($discount > 0) {
            $amount_discount = -$discount;
            $base_amount_discount = -$base_discount;
        }
        $shipping_amount = $this->price_currency->convert($this->price_currency->round($base_shipping_amount));
        $order->setBaseDiscountAmount($base_amount_discount);
        $order->setDiscountAmount($amount_discount);
        $order->setBaseTaxAmount($tax);
        $order->setTaxAmount($tax);
        $order->setBaseGrandTotal($base_sub_total - $base_discount + $base_tax + $base_shipping_amount);
        $order->setGrandTotal($sub_total - $discount + $tax + $shipping_amount);
        $order->setBaseSubtotal($base_sub_total);
        $order->setSubtotal($sub_total);
        $order->setTotalQtyOrdered($total_qty);
        $order->setBaseSubtotalInclTax($base_sub_total_incl_tax);
        $order->setSubtotalInclTax($sub_total_incl_tax);
        $order->setBaseTotalDue($base_sub_total - $base_discount + $base_tax + $base_shipping_amount);
        $order->setTotalDue($sub_total - $discount + $tax + $shipping_amount);
        $order->setBaseShippingAmount($base_shipping_amount);
        $order->setBaseShippingInclTax($base_shipping_amount);
        $order->setShippingAmount($shipping_amount);
        $order->setShippingInclTax($shipping_amount);
        $order->setShippingDescription(
            $this->getShippingDescription($order, DistributorManager::getOrderDistributorName($order))
        );
        $order->setTotalPaid($sub_total - $discount + $tax + $shipping_amount);
        $this->order_repository->save($order);

        return $order;
    }

    /**
     * Set shipping amount
     *
     * @param mixed $order
     * @param string $distributor_name
     * @param mixed $amount
     * @return mixed
     */
    private function setShippingNewAmnt($order, $distributor_name, $amount)
    {
        $method = self::GET_DISTRIBUTOR_SESSION_METHOD . $distributor_name;
        return $this->checkout_session->$method()['price'];
    }

    /**
     * Get shipping description
     *
     * @param mixed $order
     * @param string $distributor_name
     * @return string
     */
    private function getShippingDescription($order, $distributor_name)
    {
        $method = self::GET_DISTRIBUTOR_SESSION_METHOD . $distributor_name;
        $service_label = $this->checkout_session->$method()['service_label'];

        return $distributor_name . ' - ' . $service_label;
    }

    /**
     * Group order items by distributor
     *
     * @param mixed $order
     * @return array
     */
    public function groupItemsByDistributor($order)
    {
        $items_by_distributor = [];
        $items = $order->getAllItems();

        foreach ($items as $item) {
            $options = $item->getProductOptions();

            $distributor_option = array_filter($options['options'], function ($option) {
                return $option['label'] === DistributorManager::OPTION_DISTRIBUTOR_TITLE;
            });

            $items_by_distributor[$distributor_option[0]['value']][] = $item;
        }

        return $items_by_distributor;
    }

    /**
     * Spilit order items by distributor
     *
     * @param mixed $order
     * @return array
     */
    public function splitOrdersByDistributor($order)
    {
        if (empty($order->getCustomerEmail())) {
            $order->setCustomerEmail($this->customer_session->getCustomer()->getEmail());
            $this->order_repository->save($order);
        }

        // Rearrange items based on Distributor
        $list = $this->groupItemsByDistributor($order);
        if (count($list) === 1) {
            return [$order];
        }

        $base_shipping_amount = $order->getBaseShippingAmount();
        if ($base_shipping_amount) {
            $base_shipping_amount = round($order->getBaseShippingAmount() / count($list), 4);
        }

        $tax_amount = $order->getTaxAmount();
        if ($tax_amount) {
            $tax_amount = round($order->getTaxAmount() / count($list), 4);
        }

        $index = 1;
        foreach ($list as $distributor_name => $order_items) {
            if ($index > 1) {
                $new_order = $this->order_factory->create();
                $new_order->setData($this->getRequiredDataOfOrder($order->getData()));
                $payment = $order->getPayment();
                $payment->setId(null);
                $payment->setParentId(null);
                $new_order->setPayment($payment);

                $addresses = $order->getAddresses();
                foreach ($addresses as $address) {
                    $address->setId(null);
                    $address->setParentId(null);
                    $new_order->addAddress($address);
                }

                /** Save state and status value for next save to leave order pending */
                $state = $new_order->getState();
                $status = $new_order->getStatus();
                $this->order_repository->save($new_order);

                foreach ($order_items as $order_item) {
                    if ($order_item->getParentItemId()) {
                        $parent_order_item = $this->order_item_repository->get($order_item->getParentItemId());
                        $parent_order_item->setOrderId($new_order->getId());
                        $this->order_item_repository->save($parent_order_item);
                    }
                    $order_item->setOrderId($new_order->getId());
                    $this->order_item_repository->save($order_item);
                }
                /** Change state from complete */
                if ($new_order->getState() != $state || $new_order->getStatus() != $status) {
                    $new_order->setState($state);
                    $new_order->setStatus($status);
                    $this->order_repository->save($new_order);
                }

                $order = $this->setRequiredDataOfOrder(
                    $order_items,
                    $new_order,
                    $this->setShippingNewAmnt($new_order, $distributor_name, $base_shipping_amount),
                    $tax_amount
                );
                $orders[] = $order;
            } else {
                $order = $this->setRequiredDataOfOrder(
                    $order_items,
                    $order,
                    $this->setShippingNewAmnt($order, $distributor_name, $base_shipping_amount),
                    $tax_amount
                );
                $orders[] = $order;
            }
            $index++;
        }

        return $orders;
    }

    /**
     * Get order distributor info
     *
     * @param mixed $order
     * @return void
     */
    public function getOrderDistributorInfo($order)
    {
        $distributor = $this->distributor_factory->create();
        $items = $order->getAllItems();
        if (empty($items)) {
            return;
        }
        $options = $items[0]->getProductOptions();

        $distributor_option = array_filter($options['options'], function ($option) {
            return $option['label'] === DistributorManager::OPTION_DISTRIBUTOR_TITLE;
        });

        if (empty($distributor_option)) {
            return;
        }

        $distributor->load($distributor_option[0]['value'], 'name');
        $credentials = $this->credentials_manager->getCredentials(
            $distributor,
            $this->deployment_config,
            $this->customer
        );
        $service = DistributorServiceFactory::create($distributor, $credentials);

        $distributor_info = $service->getOrderDetails(['order_id' => $order->getDistributorOrderNumber()]);

        if (!empty($distributor_info->getTrackings())) {
            $this->deleteTrackingInfo($order);
            foreach ($distributor_info->getTrackings() as $tracking) {
                $this->addTrackingInfo($order, $tracking);
            }
        }

        if (!empty($distributor_info->getMacs())) {
            $this->deleteProvisioningInfo($order);
            foreach ($distributor_info->getMacs() as $provisioning) {
                $this->addProvisioningInfo($order, $provisioning);
            }
        }

        $extension_attributes = $order->getExtensionAttributes();

        $tracking_info = $this->tracking_info_factory->create();
        $tracking_info->setValue($distributor_info->getTrackings());

        $provision_info = $this->provision_info_factory->create();
        $provision_info->setValue($distributor_info->getMacs());

        $extension_attributes->setTrackingInfo($tracking_info);
        $extension_attributes->setProvisionInfo($provision_info);
        $order->setExtensionAttributes($extension_attributes);
        $order->setDistributorOrderStatus($distributor_info->getOrderStatus());
        $order->save();
    }

    /**
     * Delete order tracking info
     *
     * @param mixed $order
     * @return void
     */
    public function deleteTrackingInfo($order)
    {
        $conditions = [
            ['condition' => 'order_id = ?', 'binding' => $order->getId()]
        ];

        $this->data_repository->delete(self::ORDERS_TRACKING_TABLE, $conditions);
    }

    /**
     * Add order tracking info
     *
     * @param mixed $order
     * @param TrackingInfo $tracking_info
     * @return void
     */
    public function addTrackingInfo($order, TrackingInfo $tracking_info)
    {
        $data = [
            'order_id' => $order->getId(),
            'provider' => $tracking_info->getProvider(),
            'tracking_number' => $tracking_info->getTrackingNumber()
        ];
        $this->data_repository->insert(self::ORDERS_TRACKING_TABLE, $data);
    }

    /**
     * Get order tracking info
     *
     * @param mixed $order
     * @return mixed
     */
    public function getTrackingInfo($order)
    {
        $conditions = [
            self::ORDERS_TRACKING_TABLE . '.order_id = :order_id'
        ];
        return $this->data_repository->selectQuery(
            self::ORDERS_TRACKING_TABLE,
            $conditions,
            ['order_id' => $order->getId()]
        );
    }

    /**
     * Delete provision info
     *
     * @param mixed $order
     * @return void
     */
    public function deleteProvisioningInfo($order)
    {
        $conditions = [
            ['condition' => 'order_id = ?', 'binding' => $order->getId()]
        ];

        $this->data_repository->delete(self::ORDERS_PROVISIONING_TABLE, $conditions);
    }

    /**
     * Add provision info
     *
     * @param mixed $order
     * @param Mac $provisioning_info
     * @return void
     */
    public function addProvisioningInfo($order, Mac $provisioning_info)
    {
        $data = [
            'order_id' => $order->getId(),
            'mac' => $provisioning_info->getMac(),
            'serial' => $provisioning_info->getSerial(),
            'sku' => $provisioning_info->getSku()
        ];
        $this->data_repository->insert(self::ORDERS_PROVISIONING_TABLE, $data);
    }

    /**
     * Get provision info
     *
     * @param mixed $order
     * @return mixed
     */
    public function getProvisioningInfo($order)
    {
        $conditions = [
            self::ORDERS_PROVISIONING_TABLE . '.order_id = :order_id'
        ];
        return $this->data_repository->selectQuery(
            self::ORDERS_PROVISIONING_TABLE,
            $conditions,
            ['order_id' => $order->getId()]
        );
    }

    /**
     * Migrate Mac serials
     *
     * @param string|mixed $csv_name
     * @return false|void
     */
    public function migrateMacSerials($csv_name)
    {
        $data = [];
        $order_ids = [];
        $csvFile = $this->dir->getPath('var') . self::MACS_CSV_FILE_PATH . $csv_name;
        try {
            if ($this->file->isExists($csvFile)) {
                $this->csv->setDelimiter(",");
                $data = $this->csv->getData($csvFile);
                $column_names = $data[0];
                unset($data[0]);

                foreach ($data as $row) {
                    try {
                        $order_id = $row[array_search('order_id', $column_names)];
                        $order = $this->order_repository->get($order_id);
                        $provisioning = new Mac(
                            $row[array_search('sku', $column_names)],
                            $row[array_search('serial', $column_names)],
                            $row[array_search('mac', $column_names)]
                        );
                        if (!in_array($order_id, $order_ids)) {
                            $this->logger->info('Processing order id ' . $order_id);
                            $this->deleteProvisioningInfo($order);
                        }
                        $this->addProvisioningInfo($order, $provisioning);
                        $order_ids[] = $order_id;
                    } catch (\Exception $e) {
                        $this->logger->error(
                            'Order ID ' . $order_id . ' provisioning info could not be stored: ' . $e->getMessage()
                        );
                        continue;
                    }
                }
            } else {
                $this->logger->info(self::MACS_CSV_FILE_PATH . ' csv file does not exist.');
                return false;
            }
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}
