<?php

namespace SkySwitch\Orders\Model\Api;

use SkySwitch\Contracts\Traits\LogRequest;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use SkySwitch\Contracts\Mac;
use SkySwitch\Contracts\TrackingInfo;
use SkySwitch\Orders\Api\WebhookJenneInterface;
use SkySwitch\Orders\Model\TrackingInfoFactory;
use SkySwitch\Orders\Model\ProvisionFactory;
use SkySwitch\Orders\Managers\OrderManager;

/**
 * Class RequestItem
 */
class ProcessJenneWebhook implements WebhookJenneInterface
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
     * @var Request
     */
    protected $request;

    /**
     * @param CollectionFactory $order_collection_factory
     * @param TrackingInfoFactory $tracking_info_factory
     * @param OrderManager $order_manager
     * @param ProvisionFactory $provision_info_factory
     * @param Request $request
     */
    public function __construct(
        CollectionFactory $order_collection_factory,
        TrackingInfoFactory $tracking_info_factory,
        OrderManager $order_manager,
        ProvisionFactory $provision_info_factory,
        Request $request
    ) {
        $this->order_collection_factory = $order_collection_factory;
        $this->tracking_info_factory = $tracking_info_factory;
        $this->order_manager = $order_manager;
        $this->provision_info_factory = $provision_info_factory;
        $this->request = $request;
    }

    /**
     * Process distributor order information.
     *
     * @return void
     */
    public function processOrderInfo()
    {
        $request_body = $this->request->getContent();
        $request_body = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $request_body);
        $data = json_decode(json_encode(simplexml_load_string($request_body)), true);

        $trackings = [];
        $provisionings = [];

        $this->log('ProcessOrderInfo', Request::HTTP_METHOD_POST, $data, []);

        if (!isset($data['Body']['AdvanceShipNotice']) || empty($data['Body']['AdvanceShipNotice'])) {
            return 'No order info provided.';
        }
        $order = $data['Body']['AdvanceShipNotice'];

        $collection = $this->order_collection_factory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('distributor_order_number', $order['OrderNumber']);
        $mage_order = $collection->getFirstItem();

        if (empty($mage_order->getId())) {
            return 'Order not found.';
        }

        $this->order_manager->deleteTrackingInfo($mage_order);
        $this->order_manager->deleteProvisioningInfo($mage_order);
        foreach ($order['ASNcartons']['ASNcarton'] as $item) {
            if (!empty($item['TrackingNo'])) {
                $tracking_info = new TrackingInfo($item['ShipVia'], $item['TrackingNo']);
                $trackings[] = $tracking_info;
                $this->order_manager->addTrackingInfo($mage_order, $tracking_info);
            }

            foreach ($item['ASNcartonDetails']['ASNcartonDetail'] as $item_detail) {
                if (empty($item_detail['SerialNumber']) && empty($item_detail['MACaddress'])) {
                    continue;
                }
                $part_number = empty($item_detail['kitPartNo'])
                    ? $item_detail['PartNumber']
                    : $item_detail['kitPartNo'];
                $provisioning_info = new Mac($part_number, $item_detail['SerialNumber'], $item_detail['MACaddress']);
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
        return 'NTSDirect Webhook';
    }
}
