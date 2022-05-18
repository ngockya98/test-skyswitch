<?php

namespace SkySwitch\Orders\Model\Api;

use SkySwitch\Contracts\Traits\LogRequest;
use Magento\Framework\Webapi\Rest\Request;
use Voip888\Service\Interfaces\Request888VoipInterface;
use SkySwitch\Orders\Api\Webhook888VoipInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use SkySwitch\Contracts\Mac;
use SkySwitch\Contracts\TrackingInfo;
use SkySwitch\Orders\Model\TrackingInfoFactory;
use SkySwitch\Orders\Model\ProvisionFactory;
use SkySwitch\Orders\Managers\OrderManager;

/**
 * Class RequestItem
 */
class Process888VoipWebhook implements Webhook888VoipInterface
{
    use LogRequest;

    /**
     * @var CollectionFactory
     */
    protected $order_collection_factory;

    /**
     * @var TrackingInfoFactory
     */
    protected $tracking_info_factory;

    /**
     * @var ProvisionFactory
     */
    protected $provision_info_factory;

    /**
     * @var OrderManager
     */
    protected $order_manager;

    /**
     * @param CollectionFactory $order_collection_factory
     * @param TrackingInfoFactory $tracking_info_factory
     * @param OrderManager $order_manager
     * @param ProvisionFactory $provision_info_factory
     */
    public function __construct(
        CollectionFactory $order_collection_factory,
        TrackingInfoFactory $tracking_info_factory,
        OrderManager $order_manager,
        ProvisionFactory $provision_info_factory
    ) {
        $this->order_collection_factory = $order_collection_factory;
        $this->tracking_info_factory = $tracking_info_factory;
        $this->order_manager = $order_manager;
        $this->provision_info_factory = $provision_info_factory;
    }

    /**
     * Process distributor order information.
     *
     * @param \Voip888\Service\Interfaces\Request888VoipInterface $order
     * @return void
     */
    public function processOrderInfo($order)
    {
        $trackings = [];
        $provisionings = [];

        $this->log('ProcessOrderInfo', Request::HTTP_METHOD_POST, (array)$order, []);

        if (empty($order) || empty($order->getOrderNumber())) {
            return 'No order info provided.';
        }
        $order_number = $order->getOrderNumber();

        $collection = $this->order_collection_factory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('distributor_order_number', $order_number);
        $mage_order = $collection->getFirstItem();

        if (empty($mage_order->getId())) {
            return 'Order not found.';
        }

        $this->order_manager->deleteTrackingInfo($mage_order);
        foreach ($order->getTracking() as $tracking) {
            $tracking_info = new TrackingInfo($tracking->getProvider(), $tracking->getTrackingNumber());
            $trackings[] = $tracking_info;
            $this->order_manager->addTrackingInfo($mage_order, $tracking_info);
        }

        $this->order_manager->deleteProvisioningInfo($mage_order);
        foreach ($order->getItems() as $item) {
            foreach ($item->getSerialsAndMacs() as $provisioning) {
                $provisioning_info = new Mac($item->getSku(), $provisioning->getSerial(), $provisioning->getMac());
                $provisionings[] = $provisioning_info;
                $this->order_manager->addProvisioningInfo($mage_order, $provisioning_info);
            }
        }

        $extension_attributes = $mage_order->getExtensionAttributes();

        $tracking_info = $this->tracking_info_factory->create();
        $tracking_info->setValue($trackings);
        $extension_attributes->setTrackingInfo($tracking_info);
        $provision_info = $this->provision_info_factory->create();
        $provision_info->setValue($provisionings);
        $extension_attributes->setProvisionInfo($provision_info);
        $mage_order->setExtensionAttributes($extension_attributes);

        $mage_order->setDistributorOrderStatus($order->getOrderStatus());
        $mage_order->save();

        return 'Order successfully updated.';
    }

    /**
     * Get default Service name
     *
     * @return string
     */
    protected function getServiceName()
    {
        return '888Voip Webhook';
    }
}
