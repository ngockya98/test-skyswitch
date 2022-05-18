<?php

namespace SkySwitch\Payments\Controller\Adminhtml\Cards;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Directory\Helper\Data;
use Magedelight\Authorizecim\Helper\Data as HelperData;
use Magedelight\Authorizecim\Model\Api\Xml;
use Magedelight\Authorizecim\Model\ResourceModel\Cards\CollectionFactory;
use Magedelight\Authorizecim\Model\CardsFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\App\Action\Context;
use Magedelight\Authorizecim\Controller\Adminhtml\Cards\Authorizecim;

class Delete extends Authorizecim
{
    /**
     * @var ManagerInterface
     */
    protected $event_manager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Session $customerSession
     * @param DataObject $requestObject
     * @param TimezoneInterface $localeDate
     * @param Data $directoryHelper
     * @param HelperData $cimHelper
     * @param Xml $cimXml
     * @param CollectionFactory $cardCollFactory
     * @param CardsFactory $cardFactory
     * @param CustomerFactory $customerFactory
     * @param DataObjectFactory $objectFactory
     * @param ResourceConnection $resource
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param EncoderInterface $jsonEncoder
     * @param ManagerInterface $event_manager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $customerSession,
        DataObject $requestObject,
        TimezoneInterface $localeDate,
        Data $directoryHelper,
        HelperData $cimHelper,
        Xml $cimXml,
        CollectionFactory $cardCollFactory,
        CardsFactory $cardFactory,
        CustomerFactory $customerFactory,
        DataObjectFactory $objectFactory,
        ResourceConnection $resource,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        EncoderInterface $jsonEncoder,
        ManagerInterface $event_manager
    ) {
        $this->event_manager = $event_manager;
        return parent::__construct(
            $context,
            $registry,
            $customerSession,
            $requestObject,
            $localeDate,
            $directoryHelper,
            $cimHelper,
            $cimXml,
            $cardCollFactory,
            $cardFactory,
            $customerFactory,
            $objectFactory,
            $resource,
            $resultJsonFactory,
            $resultLayoutFactory,
            $jsonEncoder
        );
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $deleteCardId = $this->getRequest()->getParam('card_id');
        $cardModel = $this->cardFactory->create();
        $cardModel->getResource()->load($cardModel, $deleteCardId); //phpcs:ignore
        $paymentProfileId = $cardModel->getPaymentProfileId();
        $customer = $this->customerFactory->create()->load($this->getRequest()->getParam('id'));

        if ($paymentProfileId > 0) {
            $customerProfileId = $customer->getMdCustomerProfileId();
            $requestObject = new \Magento\Framework\DataObject();
            $requestObject->addData([
                'customer_profile_id' => $customerProfileId,
                'payment_profile_id' => $paymentProfileId,
            ]);
            $response = $this->cimXml->setInputData($requestObject)
                ->deleteCustomerPaymentProfile();
            $code = $response->messages->message->code;
            $resultCode = $response->messages->resultCode;
            $code = 'I00001';
            $resultCode = 'Ok';
            $isSuccess = false;
            if ($code == 'I00001' && $resultCode == 'Ok') {
                $cardModel->getResource()->delete($cardModel); //phpcs:ignore
                $message = '<div id="messages"><div class="messages">
                <div class="message message-success success">
                <div data-ui-id="messages-message-success">
                '.__('Card deleted successfully.').'
                </div></div></div></div>';
                $result = ['error' => false, 'message' => $message];

                $this->event_manager->dispatch(
                    'cc_deleted',
                    [
                        'cc_data' => $cardModel->getData(),
                        'customer' => $customer
                    ]
                );

            } else {
                $message = '<div id="messages"><div class="messages">
                <div class="message message-error error">
                <div data-ui-id="messages-message-error">
                '.$response->site_display_message.'
                </div></div></div></div>';
                $result = ['error' => true, 'message' => $message];
            }
        } else {
            $message = '<div id="messages"><div class="messages">
            <div class="message message-error error">
            <div data-ui-id="messages-message-error">
            '.__('Card does not exists.').'
            </div></div></div></div>';
            $result = ['error' => true, 'message' => $message];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
}
