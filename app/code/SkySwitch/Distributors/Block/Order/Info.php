<?php
namespace SkySwitch\Distributors\Block\Order;

use Magento\Sales\Block\Order\Info as SalesInfo;
use SkySwitch\Distributors\Managers\DistributorManager;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use SkySwitch\Orders\Model\CustomSession;
use SkySwitch\Orders\Managers\OrderManager;

class Info extends SalesInfo
{
    /**
     * @var string
     */
    protected $_template = 'SkySwitch_Distributors::order/info.phtml';

    /**
     * @var CustomSession
     */
    protected $custom_session;

    /**
     * @var OrderManager
     */
    protected $order_manager;

    /**
     * @param CustomSession $custom_session
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param OrderManager $order_manager
     */
    public function __construct(
        CustomSession $custom_session,
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        OrderManager $order_manager
    ) {
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer);
        $this->custom_session = $custom_session;
        $this->order_manager = $order_manager;
    }

    /**
     * Return distributor name
     *
     * @return mixed|string
     */
    public function getDistributorName()
    {
        return DistributorManager::getOrderDistributorName($this->getOrder());
    }

    /**
     * Return order tracking info
     *
     * @return mixed
     */
    public function getTrackingInfo()
    {
        return $this->order_manager->getTrackingInfo($this->getOrder());
    }

    /**
     * Return order provision info
     *
     * @return mixed
     */
    public function getProvisionInfo()
    {
        return $this->order_manager->getProvisioningInfo($this->getOrder());
    }
}
