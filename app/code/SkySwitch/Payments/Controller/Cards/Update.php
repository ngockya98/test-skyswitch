<?php

namespace SkySwitch\Payments\Controller\Cards;

use Magedelight\Authorizecim\Controller\Cards\Update as MageUpdate;
use Magento\Framework\Event\ManagerInterface;
use Magento\Backend\App\Action\Context;
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
use Magento\Framework\Registry;

class Update extends MageUpdate
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
            $resource
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $errorMessage = '';
        $customerId = $this->customerSession->getCustomerId();
        $customerModel = $this->customerFactory->create();
        $customerModel->getResource()->load($customerModel, $customerId);
        $customerProfileId = $customerModel->getMdCustomerProfileId();
        $params = $this->getRequest()->getParams();

        if (empty($params['md_authorizecim']['payment_info']['cc_cid'])) {
            $this->messageManager->addError(__(
                'Please enter the Card Verification Number'
            ));
            $this->_redirect('md_authorizecim/cards/lists');
        }

        if ($this->_directoryHelper->isRegionRequired($params['country_id'])) {
            $params['md_authorizecim']['address_info']['state'] = '';
        } else {
            $params['md_authorizecim']['address_info']['region_id'] = 0;
        }
        $params['md_authorizecim']['address_info']['country_id'] = $params['country_id'];
        if (isset($params['md_authorizecim']['payment_info']['cc_action'])) {
            $ccAction = $params['md_authorizecim']['payment_info']['cc_action'];
        }
        if ($ccAction == 'existing') {
            unset($params['md_authorizecim']['payment_info']['cc_number']);
            unset($params['md_authorizecim']['payment_info']['cc_type']);
            unset($params['md_authorizecim']['payment_info']['cc_exp_month']);
            unset($params['md_authorizecim']['payment_info']['cc_exp_year']);
        }
        $readAdapter = $this->dbResource->getConnection('core_read');
        $writeAdapter = $this->dbResource->getConnection('core_write');

        $query1 = "SELECT `attribute_id` FROM `{$this->dbResource->getTableName('eav_attribute')}` "
            . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";

        $eavAttributeId = $readAdapter->fetchOne($query1);

        $query2 = "SELECT `value_id` FROM `{$this->dbResource->getTableName('customer_entity_varchar')}` "
            . "WHERE `entity_id`='{$customerId}' AND `attribute_id`='{$eavAttributeId}'";
        $valueId = (int) $readAdapter->fetchOne($query2);
        $requestObject = $this->objectFactory->create();
        try {
            if ($customerProfileId==null) {
                $requestObject->addData([
                    'customer_id' => $customerId,
                    'email' => $this->customerSession->getCustomer()->getEmail(),
                ]);
                $response = $this->cimXml
                    ->setInputData($requestObject)
                    ->createCustomerProfile();
                $code =  $response->messages->message->code;
                $resultCode = $response->messages->resultCode;
                $customerProfileId = $response->customerProfileId;
                if ($code == 'I00001' && $resultCode == 'Ok') {
                    if ($valueId <= 0) {
                        $Query = "INSERT INTO `{$this->dbResource->getTableName('customer_entity_varchar')}` (attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerId}','{$customerProfileId}')";
                    } else {
                        $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' AND `entity_id`='{$customerId}'";
                    }
                    $writeAdapter->query($Query);
                } elseif ($code == 'E00039' && strpos($response->messages->message->text, 'duplicate') !== false) {
                    $customerProfileId = preg_replace('/[^0-9]/', '', $response->messages->message->text);
                    if ($valueId <= 0) {
                        $Query = "INSERT INTO `{$this->dbResource->getTableName('customer_entity_varchar')}` (attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerId}','{$customerProfileId}')";
                    } else {
                        $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' AND `entity_id`='{$customerId}'";
                    }
                    $writeAdapter->query($Query);
                } else {
                    $errorMessage .= $response->messages->message->text;
                }
            }
            if (is_string($errorMessage) && strlen($errorMessage) > 0) {
                $this->messageManager->addError(__($errorMessage));
            } else {

                $requestObject->addData($params['md_authorizecim']['address_info']);
                $requestObject->addData($params['md_authorizecim']['payment_info']);
                $requestObject->addData(['country_id' => $params['country_id']]);
                $requestObject->addData(['customer_profile_id' => $customerProfileId]);
                $paymentProfileId = $params['md_authorizecim']['payment_profile_id'];
                $requestObject->addData(['payment_profile_id' =>
                    $paymentProfileId,
                    'card_number_masked' => "XXXX" . $params['md_authorizecim']['card_number_masked']]);
                $response = $this->cimXml
                    ->setInputData($requestObject)
                    ->updateCustomerPaymentProfile();

                $code = $response->messages->message->code;
                $resultCode = $response->messages->resultCode;
                $updateCardId = $params['md_authorizecim']['card_id'];
                $cardModel = $this->cardFactory->create()->load($updateCardId);
                $oldCardData = $cardModel->getData();
                $old_card_data = $oldCardData;
                unset($oldCardData['card_id']);

                $model = $this->cardFactory->create();
                $model->load($updateCardId);
                $model
                    ->setData($oldCardData);
                $model->setData($params['md_authorizecim']['address_info']);
                $cardUpdateCheck = $params['md_authorizecim']['payment_info'];
                if ($cardUpdateCheck['cc_action'] == 'existing') {
                    $model->setccType($oldCardData['cc_type'])
                        ->setcc_exp_month($oldCardData['cc_exp_month'])
                        ->setcc_exp_year($oldCardData['cc_exp_year']);
                    if (isset($oldCardData['cc_last4'])):
                        $model->setcc_last4($oldCardData['cc_last4']);
                    endif;
                } else {
                    $requestObject = new \Magento\Framework\DataObject();
                    $requestObject->addData([
                        'customer_profile_id' => $customerProfileId,
                        'payment_profile_id' => $paymentProfileId,
                    ]);
                    $paymentdetailResponse  = $this->cimXml->setInputData($requestObject)
                        ->getCustomerPaymentProfile();
                    $code = (string)$paymentdetailResponse->messages->message->code;
                    $resultCode =  (string)$paymentdetailResponse->messages->resultCode;
                    if ($code == 'I00001' && $resultCode == 'Ok') {
                        $paymentdetail = $paymentdetailResponse->paymentProfile->payment;
                        $ccType = (isset($this->cardArray[(string)$paymentdetail->creditCard->cardType]))
                            ? $this->cardArray[(string)$paymentdetail->creditCard->cardType] : '' ;
                        $ccExpDate = $paymentdetail->creditCard->expirationDate;
                        if ($ccExpDate!='') {
                            $expDAte = explode('-', $ccExpDate);
                            if (count($expDAte)>1) {
                                $ccExpMonth = $expDAte[1];
                                $ccExpYear = $expDAte[0];
                            } else {
                                $ccExpMonth = '';
                                $ccExpYear = '';
                            }
                        } else {
                            $ccExpMonth = '';
                            $ccExpYear = '';
                        }
                        $ccLast4 = substr((string)$paymentdetail->creditCard->cardNumber, -4, 4);
                    } else {
                        $this->messageManager
                            ->addError(new \Magento\Framework\Phrase(__($response->messages->message->text)));
                        $this->_redirect('md_authorizecim/cards/lists');
                        return;
                    }
                    $model->setccType($ccType)
                        ->setcc_exp_month($ccExpMonth)
                        ->setcc_exp_year($ccExpYear)
                        ->setcc_last4($ccLast4);
                }
                $model->setCustomerId($customerModel->getId())
                    ->setUpdatedAt(date('Y-m-d H:i:s'))
                    ->setCardId($updateCardId);
                $model->save();
                if ($code == 'I00001' && $resultCode == 'Ok') {
                    $this->messageManager->addSuccess(__('Credit card saved successfully.'));

                    $args = [
                        'name_first' => $params['md_authorizecim']['address_info']['firstname'],
                        'name_last' => $params['md_authorizecim']['address_info']['lastname'],
                        'card_number' => $params['md_authorizecim']['payment_info']['cc_number'] ?? '',
                        'expiration_month' => $params['md_authorizecim']['payment_info']['cc_exp_month'] ?? '',
                        'expiration_year' => $params['md_authorizecim']['payment_info']['cc_exp_year'] ?? '',
                        'cvv' => $params['md_authorizecim']['payment_info']['cc_cid']
                    ];

                    $this->event_manager->dispatch(
                        'cc_updated',
                        [
                            'old_card_data' => $old_card_data,
                            'new_card_data' => $args,
                            'customer' => $customerModel
                        ]
                    );
                } else {
                    $this->messageManager->addError(__($response->messages->message->text));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__(
                'We can\'t add the credit card to your account right now: %1.',
                $e->getMessage()
            ));
        }
        $this->_redirect('md_authorizecim/cards/lists');
    }
    // @codingStandardsIgnoreEnd
}
