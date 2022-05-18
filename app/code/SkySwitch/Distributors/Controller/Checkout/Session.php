<?php

namespace SkySwitch\Distributors\Controller\Checkout;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use SkySwitch\Orders\Managers\OrderManager;

class Session implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $json_factory;

    /**
     * @var CheckoutSession
     */
    protected $session;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param JsonFactory $json_factory
     * @param CheckoutSession $session
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $json_factory,
        CheckoutSession $session,
        RequestInterface $request
    ) {
        $this->json_factory = $json_factory;
        $this->session = $session;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $distributors = $this->request->getParam('distributors', []);

        foreach ($distributors as $distributor) {
            $uns_method = OrderManager::UNSET_DISTRIBUTOR_SESSION_METHOD . $distributor['distributor'];
            $set_method = OrderManager::SET_DISTRIBUTOR_SESSION_METHOD . $distributor['distributor'];
            $this->session->$uns_method();
            $this->session->$set_method([
                'price' => $distributor['price'],
                'service_label' => $distributor['service_label'],
                'distributor' => $distributor['distributor']
            ]);
        }

        $this->session->unsShippingPrice();
        $this->session->setShippingPrice((float)$this->request->getParam('price'));

        $result = $this->json_factory->create();
        return $result->setData(['data' => 'success']);
    }
}
