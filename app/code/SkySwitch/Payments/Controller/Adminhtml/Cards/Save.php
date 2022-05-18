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

class Save extends Authorizecim
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

    // @codingStandardsIgnoreStart
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $errorMessage = '';
        $params = $this->getRequest()->getParams();
        $customerId = $params['id'];
        $customerCardData = $params['paymentParam'];
        if ($this->_directoryHelper->isRegionRequired($customerCardData['country_id'])) {
            $customerCardData['state'] = '';
        } else {
            $customerCardData['region_id'] = 0;
        }

        $ccAction = 'new';
        if (isset($customerCardData['cc_action'])) {
            $ccAction = $customerCardData['cc_action'];
        }
        $append = '';
        $customerModel = $this->customerFactory->create();
        $customerModel->getResource()->load($customerModel, $customerId);
        $customerProfileId = $customerModel->getMdCustomerProfileId();
        $requestObject = new \Magento\Framework\DataObject();
        try {
            if (!$customerProfileId) {
                //to created customer profile id if it is not exists for that customer
                $requestObject->addData([
                    'customer_id' => $customerModel->getId(),
                    'email' => $customerModel->getEmail(),
                ]);
                $response = $this->cimXml->setInputData($requestObject)
                    ->createCustomerProfile();
                $code = $response->messages->message->code;
                $resultCode = $response->messages->resultCode;
                $customerProfileId = $response->customerProfileId;
                if ($code == 'I00001' && $resultCode == 'Ok') {
                    $paymentProfileId = (string) $response->customerPaymentProfileId;
                    $readAdapter = $this->dbResource->getConnection('core_read');
                    $writeAdapter = $this->dbResource->getConnection('core_write');
                    $query1 = "SELECT `attribute_id` FROM `{$this->dbResource->getTableName('eav_attribute')}` "."WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";
                    $eavAttributeId = $readAdapter->fetchOne($query1);
                    $query2 = "SELECT `value_id` FROM `{$this->dbResource
                        ->getTableName('customer_entity_varchar')}` "
                        . "WHERE `entity_id`='{$customerId}' AND `attribute_id`='{$eavAttributeId}'";
                    $valueId = (int) $readAdapter->fetchOne($query2);
                    if ($valueId <= 0) {
                        $Query = "INSERT INTO `{$this->dbResource->getTableName('customer_entity_varchar')}` (attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerId}','{$customerProfileId}')";
                    } else {
                        $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` "
                            . "SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' "
                            . "AND `entity_id`='{$customerId}'";
                    }
                    $writeAdapter->query($Query);
                } elseif ($code == 'E00039' && strpos($response->messages->message->text, 'duplicate') !== false) {

                    $customerProfileId = preg_replace('/[^0-9]/', '', $response->messages->message->text);

                    $readAdapter = $this->dbResource->getConnection('core_read');
                    $writeAdapter = $this->dbResource->getConnection('core_write');
                    $query1 = "SELECT `attribute_id` FROM `{$this->dbResource->getTableName('eav_attribute')}` "
                        . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";
                    $eavAttributeId = $readAdapter->fetchOne($query1);
                    $query2 = "SELECT `value_id` FROM `{$this->dbResource->getTableName('customer_entity_varchar')}` WHERE `entity_id`='{$customerId}' AND `attribute_id`='{$eavAttributeId}'";
                    $valueId = (int) $readAdapter->fetchOne($query2);
                    if ($valueId <= 0) {
                        $Query = "INSERT INTO `{$this->dbResource->getTableName('customer_entity_varchar')}` (attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerId}','{$customerProfileId}')";
                    } else {
                        $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' AND `entity_id`='{$customerId}'";
                    }
                    $writeAdapter->query($Query);
                } else {
                    $errorMessage = $response->messages->message->text;
                }
            }
            if (is_string($errorMessage) && strlen($errorMessage) > 0) {
                $append = '<div id="messages"><div class="messages"><div class="message message-error error">'
                    . '<div data-ui-id="messages-message-error">'.$errorMessage.'</div></div></div></div>';
                $result = ['error' => true, 'message' => $append];
            } else {
                $requestObject->addData($customerCardData);
                $requestObject->addData(['customer_profile_id' => $customerProfileId]);

                $response = $this->cimXml->setInputData($requestObject)
                    ->createCustomerPaymentProfile();

                $code = $response->messages->message->code;
                $resultCode = $response->messages->resultCode ;
                $wasDuplicate = false;
                if ($code == 'E00039' && strpos($response->messages->message->text, 'duplicate') !== false) {
                    $existingProfiles = $this->cimXml->setInputData(new \Magento\Framework\DataObject([
                        'customer_profile_id' => $customerProfileId]))->getCustomerProfile();
                    $profiles = $existingProfiles->profile->paymentProfiles;
                    $lastFour = substr($customerCardData['cc_number'], -4);
                    if (count($profiles) > 1) {
                        foreach ($profiles as $paymentProfiles) {
                            $existingCard = substr((string) $paymentProfiles->payment->creditCard->cardNumber, -4);
                            if ($lastFour == $existingCard) {
                                $wasDuplicate = true;
                                $paymentProfileId = (string) $paymentProfiles->customerPaymentProfileId;
                            }
                        }
                    } elseif (count($profiles) == 1) {
                        $existingCard = substr((string) $profiles->payment->creditCard->cardNumber, -4);
                        if ($lastFour == $existingCard) {
                            $wasDuplicate = true;
                            $paymentProfileId = (string) $profiles->customerPaymentProfileId;
                        }
                    }
                    if ($wasDuplicate && strlen($paymentProfileId) > 0) {
                        $requestObject->addData([
                            'customer_profile_id' => $customerProfileId,
                            'payment_profile_id' => $paymentProfileId]);
                        $response = $this->cimXml->setInputData($requestObject)
                            ->updateCustomerPaymentProfile();
                        $code = $response->messages->message->code;
                        $resultCode = $response->messages->resultCode;
                    }
                }

                if ($code == 'I00001' && $resultCode == 'Ok') {
                    $paymentProfileId = (string) $response->customerPaymentProfileId;
                    $cardModelObject = $this->cardFactory->create();
                    $cardModelObject->setData($customerCardData)
                        ->setCustomerId($customerModel->getId())
                        ->setCustomerProfileId($customerProfileId)
                        ->setPaymentProfileId($paymentProfileId)
                        ->setCcType($customerCardData['cc_type'])
                        ->setCcExpMonth($customerCardData['cc_exp_month'])
                        ->setCcExpYear($customerCardData['cc_exp_year'])
                        ->setCcLast4(substr($customerCardData['cc_number'], -4, 4))
                        ->setCountryId($customerCardData['country_id'])
                        ->setCreatedAt($this->localeDate->date())
                        ->setUpdatedAt($this->localeDate->date());
                    $cardModelObject->getResource()->save($cardModelObject);

                    $append = '<div id="messages"><div class="messages">
                    <div class="message message-success success">
                    <div data-ui-id="messages-message-success">
                    Credit card saved successfully.</div></div></div></div>';
                    $cimBlock = $this->_view->getLayout()->createBlock(
                        'Magedelight\Authorizecim\Block\Adminhtml\CardTab'
                    );
                    $cimBlock->setChild('authorizecimAddCards', $this->_view->getLayout()->createBlock(
                        'Magedelight\Authorizecim\Block\Adminhtml\CardForm'
                    ));
                    $cimBlock->setCustomerId($customerId);
                    $carddata = $cimBlock->toHtml();

                    $args = [
                        'name_first' => $params['paymentParam']['firstname'],
                        'name_last' => $params['paymentParam']['lastname'],
                        'card_number' => $params['paymentParam']['cc_number'],
                        'expiration_month' => $params['paymentParam']['cc_exp_month'],
                        'expiration_year' => $params['paymentParam']['cc_exp_year'],
                        'cvv' => $params['paymentParam']['cc_cid']
                    ];

                    $this->event_manager->dispatch(
                        'cc_added',
                        [
                            'cc_data' => $args,
                            'customer' => $customerModel
                        ]
                    );

                    $result = ['error' => false, 'message' => $append, 'carddata' => $carddata];
                } else {
                    $append = '<div id="messages"><div class="messages">
                    <div class="message message-error error">
                    <div data-ui-id="messages-message-error">'
                        .$response->messages->message->text.
                        '</div></div></div></div>';
                    $result = ['error' => true, 'message' => $append];
                }
            }

        } catch (\Exception $e) {
            $append = '<div id="messages"><div class="messages">
            <div class="message message-error error">
            <div data-ui-id="messages-message-error">'.$e->getMessage().'</div></div></div></div>';
            $result = ['error' => true, 'message' => $append];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);

        return $resultJson;
    }
    // @codingStandardsIgnoreEnd
}
