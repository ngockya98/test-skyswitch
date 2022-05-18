<?php

namespace SkySwitch\Payments\Controller\Cards;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Directory\Helper\Data;
use Magedelight\Authorizecim\Helper\Data as MageData;
use Magedelight\Authorizecim\Model\Api\Xml;
use Magedelight\Authorizecim\Model\ResourceModel\Cards\CollectionFactory;
use Magedelight\Authorizecim\Model\CardsFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magedelight\Authorizecim\Controller\Cards\Authorizecim;

class Save extends Authorizecim
{
    /**
     * @var ManagerInterface
     */
    protected $event_manager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Session $customer_session
     * @param DataObject $request_object
     * @param TimezoneInterface $locale_date
     * @param Data $directory_helper
     * @param MageData $cim_helper
     * @param Xml $cim_xml
     * @param CollectionFactory $card_coll_factory
     * @param CardsFactory $card_factory
     * @param CustomerFactory $customer_factory
     * @param DataObjectFactory $object_factory
     * @param ResourceConnection $resource
     * @param ManagerInterface $event_manager
     */
    public function __construct(
        Context            $context,
        Registry           $registry,
        Session            $customer_session,
        DataObject         $request_object,
        TimezoneInterface  $locale_date,
        Data               $directory_helper,
        MageData           $cim_helper,
        Xml                $cim_xml,
        CollectionFactory  $card_coll_factory,
        CardsFactory       $card_factory,
        CustomerFactory    $customer_factory,
        DataObjectFactory  $object_factory,
        ResourceConnection $resource,
        ManagerInterface   $event_manager
    ) {
        $this->event_manager = $event_manager;
        parent::__construct(
            $context,
            $registry,
            $customer_session,
            $request_object,
            $locale_date,
            $directory_helper,
            $cim_helper,
            $cim_xml,
            $card_coll_factory,
            $card_factory,
            $customer_factory,
            $object_factory,
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
        $readAdapter = $this->dbResource->getConnection('core_read');
        $writeAdapter = $this->dbResource->getConnection('core_write');
        $websiteId = $this->cimHelper->getWebsiteId();

        $query1 = "SELECT `attribute_id` FROM `{$this->dbResource->getTableName('eav_attribute')}` "
            . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";

        $eavAttributeId = $readAdapter->fetchOne($query1);

        $query2 = "SELECT `value_id` FROM `{$this->dbResource->getTableName('customer_entity_varchar')}` "
            . "WHERE `entity_id`='{$customerId}' AND `attribute_id`='{$eavAttributeId}'";
        $valueId = (int)$readAdapter->fetchOne($query2);
        $requestObject = $this->objectFactory->create();
        try {
            if ($customerProfileId == null) {
                $requestObject->addData([
                    'customer_id' => $customerId,
                    'email' => $this->customerSession->getCustomer()->getEmail(),
                ]);
                $response = $this->cimXml
                    ->setInputData($requestObject)
                    ->createCustomerProfile();
                $code = $response->messages->message->code;
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
                $response = $this->cimXml
                    ->setInputData($requestObject)
                    ->createCustomerPaymentProfile();
                $code = $response->messages->message->code;
                $resultCode = $response->messages->resultCode;

                if ($code == 'I00001' && $resultCode == 'Ok') {
                    $paymentProfileId = (string)$response->customerPaymentProfileId;
                } elseif ($code == 'E00039' && strpos($response->messages->message->text, 'duplicate') !== false) {
                    $paymentProfileId = (string)$response->customerPaymentProfileId;
                } else {
                    $this->messageManager->addError(
                        new \Magento\Framework\Phrase(__($response->messages->message->text))
                    );
                    $this->_redirect('md_authorizecim/cards/lists');
                    return;
                }
                $requestObject = new \Magento\Framework\DataObject();
                $requestObject->addData([
                    'customer_profile_id' => $customerProfileId,
                    'payment_profile_id' => $paymentProfileId,
                ]);
                $paymentdetailResponse = $this->cimXml->setInputData($requestObject)
                    ->getCustomerPaymentProfile();
                $code = (string)$paymentdetailResponse->messages->message->code;
                $resultCode = (string)$paymentdetailResponse->messages->resultCode;
                if ($code == 'I00001' && $resultCode == 'Ok') {
                    $paymentdetail = $paymentdetailResponse->paymentProfile->payment;
                    $ccType = (isset($this->cardArray[(string)$paymentdetail->creditCard->cardType]))
                        ? $this->cardArray[(string)$paymentdetail->creditCard->cardType] : '';
                    $ccExpDate = $paymentdetail->creditCard->expirationDate;
                    if ($ccExpDate != '') {
                        $expDAte = explode('-', $ccExpDate);
                        if (count($expDAte) > 1) {
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

                $cardModelObject = $this->cardFactory->create();
                $cardModelObject->setData($params['md_authorizecim']['address_info'])
                    ->setCustomerId($customerModel->getId())
                    ->setCustomerProfileId($customerProfileId)
                    ->setPaymentProfileId($paymentProfileId)
                    ->setCcType($ccType)
                    ->setCcExpMonth($ccExpMonth)
                    ->setCcExpYear($ccExpYear)
                    ->setCcLast4($ccLast4)
                    ->setCountryId($params['country_id'])
                    ->setWebsiteId($websiteId)
                    ->setCreatedAt($this->localeDate->date())
                    ->setUpdatedAt($this->localeDate->date());
                $cardModelObject->getResource()->save($cardModelObject);

                $args = [
                    'name_first' => $params['md_authorizecim']['address_info']['firstname'],
                    'name_last' => $params['md_authorizecim']['address_info']['lastname'],
                    'card_number' => $params['md_authorizecim']['payment_info']['cc_number'],
                    'expiration_month' => $params['md_authorizecim']['payment_info']['cc_exp_month'],
                    'expiration_year' => $params['md_authorizecim']['payment_info']['cc_exp_year'],
                    'cvv' => $params['md_authorizecim']['payment_info']['cc_cid']
                ];

                $this->event_manager->dispatch(
                    'cc_added',
                    [
                        'cc_data' => $args,
                        'customer' => $customerModel
                    ]
                );

                $this->messageManager->addSuccess(__('Credit card saved successfully.'));
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
