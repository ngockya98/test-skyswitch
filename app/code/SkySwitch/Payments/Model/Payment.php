<?php

namespace SkySwitch\Payments\Model;

use Magedelight\Authorizecim\Model\Payment as MagePayment;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Revio\Service\Revio;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory
    as TransactionCollectionFactory;
use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Customer\Model\Session as customerSession;
use Magento\Framework\Event\ManagerInterface;

class Payment extends MagePayment
{
    /**
     * @var ManagerInterface
     */
    protected $event_manager;

    /**
     * @param ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param TransactionCollectionFactory $salesTransactionCollectionFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param checkoutSession $checkoutsession
     * @param \Magento\Sales\Model\OrderFactory $ordermodelFactory
     * @param \Magento\Payment\Model\Config $paymentconfig
     * @param \Magedelight\Authorizecim\Model\CardsFactory $cardFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Sales\Model\Order\Payment\Transaction $paymentTrans
     * @param customerSession $customerSession
     * @param \Magento\Framework\App\Request\Http $requestHttp
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magedelight\Authorizecim\Model\Config $cimConfig
     * @param \Magedelight\Authorizecim\Model\Api\Xml $cimXml
     * @param MagePayment\Cards $cardpayment
     * @param \Magedelight\Authorizecim\Helper\Data $cimHelper
     * @param \Magedelight\Authorizecim\Model\ResourceModel\Cards\CollectionFactory $cardCollectionFactory
     * @param TransactionCollectionFactory $transactionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ManagerInterface $event_manager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TransactionCollectionFactory $salesTransactionCollectionFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\DataObjectFactory $objectFactory,
        checkoutSession $checkoutsession,
        \Magento\Sales\Model\OrderFactory $ordermodelFactory,
        \Magento\Payment\Model\Config $paymentconfig,
        \Magedelight\Authorizecim\Model\CardsFactory $cardFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Order\Payment\Transaction $paymentTrans,
        customerSession $customerSession,
        \Magento\Framework\App\Request\Http $requestHttp,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magedelight\Authorizecim\Model\Config $cimConfig,
        \Magedelight\Authorizecim\Model\Api\Xml $cimXml,
        \Magedelight\Authorizecim\Model\Payment\Cards $cardpayment,
        \Magedelight\Authorizecim\Helper\Data $cimHelper,
        \Magedelight\Authorizecim\Model\ResourceModel\Cards\CollectionFactory $cardCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory $transactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ManagerInterface $event_manager
    ) {
        $this->event_manager = $event_manager;

        parent::__construct(
            $eventManager,
            $registry,
            $storeManager,
            $scopeConfig,
            $salesTransactionCollectionFactory,
            $regionFactory,
            $orderRepository,
            $objectFactory,
            $checkoutsession,
            $ordermodelFactory,
            $paymentconfig,
            $cardFactory,
            $date,
            $paymentTrans,
            $customerSession,
            $requestHttp,
            $encryptor,
            $sessionQuote,
            $customerRepository,
            $cimConfig,
            $cimXml,
            $cardpayment,
            $cimHelper,
            $cardCollectionFactory,
            $transactionFactory,
            $customerFactory,
            $localeDate,
            $resource,
            $objectManager
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * Capture function
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param mixed $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(__('Invalid amount for capture.'))
            );
        }
        $this->_initCardsStorage($payment);

        if (empty($this->_postData)) {
            $this->_postData = $this->registry->registry('postdata');
        }
        $post = $this->_postData;
        $saveCard = 'false';
        if (isset($post['save_card']) && !empty($post['save_card'])) {
            $saveCard = $post['save_card'];
        }

        $threedActive = $this->getThreedActive($payment);
        try {
            if ($this->_isPreauthorizeCapture($payment)) {
                $this->_preauthorizeCapture($payment, $amount);
            } else {
                $isMultiShipping = $this->checkoutsession->getQuote()->getData('is_multi_shipping');
                $saveFlag = 'false';
                if ((isset($post['payment_profile_id'])
                        && !empty($post['payment_profile_id'])
                        && empty($post['cc_number'])) || ($isMultiShipping == '1'
                        && !empty($post['payment_profile_id']))
                ) {
                    $paymentProfileCheck = $this->encryptor->decrypt($post['payment_profile_id']);
                    $payment->setMdPaymentProfileId($paymentProfileCheck);

                    // Here I need to set the customer_profile_id and payment_profile_id from Rev.io
                    $profile_pieces = explode('::', $paymentProfileCheck);

                    if (count($profile_pieces) === 4) {
                        $payment->setAdditionalInformation('md_payment_profile_id', $profile_pieces[1]);
                        $payment->setAdditionalInformation('md_customer_profile_id', $profile_pieces[0]);
                    } else {
                        $payment->unsAdditionalInformation();
                        throw new LocalizedException(new Phrase('Card does not exists'));
                    }

                    $response = $this->cimXml
                        ->prepareCaptureResponse($payment, $amount, $saveFlag, $threedActive, true);
                } else {
                    if ((($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != '' ||
                            ($saveCard == 'true' && isset($post['data_value'])) && $post['data_value'] != '')
                        && ($this->customerSession->getCustomerId() || ($this->cimHelper->checkAdmin()
                                && $this->objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId())) //phpcs:ignore
                    ) {

                        /* check customer profile id is exist or not */
                        $customerid = $payment->getOrder()->getCustomerId();
                        $customerModel = $this->customerFactory->create();
                        $customerModel->getResource()->load($customerModel, $customerid); //phpcs:ignore
                        $customerProfileId = $customerModel->getMdCustomerProfileId();

                        if ($customerProfileId=='') {
                            $saveFlag = 'true';
                        }
                    }
                    $response = $this->cimXml
                        ->prepareCaptureResponse($payment, $amount, $saveFlag, $threedActive, false);
                }

                $code = $response->messages->message->code;
                $resultCode =  $response->messages->resultCode;
                if ($code == 'I00001' && $resultCode == 'Ok') {
                    $transResponse = $response->transactionResponse;
                    if (!in_array((string)$transResponse->responseCode, [2, 3])) {
                        if (!empty($paymentProfileCheck) && empty($post['cc_number'])) {
                            $profile_pieces = explode('::', $paymentProfileCheck);
                            $payment->setCcLast4($profile_pieces[2]);
                            $payment->setCcType($profile_pieces[3]);
                            $payment->setAdditionalInformation('md_payment_profile_id', $profile_pieces[1]);
                            $payment->setMdPaymentProfileId($paymentProfileCheck);
                        } else {
                            $payment->setCcLast4(substr((string)$transResponse->accountNumber, -4, 4));
                            if (isset($this->cardArray[(string)$transResponse->accountType])):
                                $cardType = $this->cardArray[(string)$transResponse->accountType];
                            elseif (isset($post['cc_type'])):
                                $cardType = $post['cc_type'];
                            else:
                                $cardType = '';
                            endif;
                            $payment->setCcType($cardType);
                        }
                        $saveCard = $payment->getData('additional_information', 'md_save_card');
                        if ((($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != '' ||
                                ($saveCard == 'true' && isset($post['data_value'])) && $post['data_value'] != '')
                            && ($this->customerSession->getCustomerId()
                                || ($this->cimHelper->checkAdmin()
                                    && $this->objectManager->get('Magento\Backend\Model\Session\Quote')->getQuote()->getCustomerId())) //phpcs:ignore
                        ) {
                            if ($saveFlag=='true') {
                                /* save card from profile response */
                                $profileResponse = $response->profileResponse;
                                $resultCode =  (string)$profileResponse->messages->resultCode;
                                $code = (string)$profileResponse->messages->message->code;
                                if ($code == 'I00001' && $resultCode == 'Ok') {
                                    $customerid = $this->cimHelper->checkAdmin() ?
                                        $this->objectManager->get('Magento\Backend\Model\Session\Quote')
                                            ->getQuote()->getCustomerId() : $this->customerSession->getCustomer()->getId();
                                    if ($customerid=='') {
                                        $customerid = $payment->getOrder()->getCustomerId();
                                    }
                                    $this->saveCustomerProfileData($profileResponse, $payment, $customerid);
                                } elseif ($code == 'E00039' &&
                                    strpos($profileResponse->messages->message->text, 'duplicate') !== false) {
                                    $customerProfileId = preg_replace(
                                        '/[^0-9]/',
                                        '',
                                        $profileResponse->messages->message->text
                                    );
                                    $readAdapter = $this->dbResource->getConnection('core_read');
                                    $writeAdapter = $this->dbResource->getConnection('core_write');
                                    $query1 = "SELECT `attribute_id` FROM `{$this->dbResource
                                    ->getTableName('eav_attribute')}` "
                                        . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";

                                    $eavAttributeId = $readAdapter->fetchOne($query1);

                                    $query2 = "SELECT `value_id` FROM `{$this->dbResource
                                    ->getTableName('customer_entity_varchar')}` "
                                        . "WHERE `entity_id`='{$customerid}' AND `attribute_id`='{$eavAttributeId}'";
                                    $valueId = (int) $readAdapter->fetchOne($query2);
                                    if ($valueId <= 0) {
                                        $Query = "INSERT INTO `{$this->dbResource
                                    ->getTableName('customer_entity_varchar')}` "
                                            . "(attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerid}',"
                                            . "'{$customerProfileId}')";
                                    } else {
                                        $Query ="UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` "
                                            . "SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' "
                                            . "AND `entity_id`='{$customerid}'";
                                    }
                                    $writeAdapter->query($Query);
                                    $this->prepareProfileResponse(
                                        $payment,
                                        $customerProfileId,
                                        (string)$transResponse->transId
                                    );

                                } elseif ($code == 'E00103') {
                                    $customerProfileRes = $this->cimXml->createCustPaymentProfileFromTransaction(
                                        (string)$transResponse->transId
                                    );
                                    $code = $customerProfileRes->messages->message->code;
                                    $resultCode = $response->messages->resultCode;
                                    if ($code == 'I00001' && $resultCode == 'Ok') {
                                        $customerProfileId = (string)$customerProfileRes->customerProfileId;
                                        $readAdapter = $this->dbResource->getConnection('core_read');
                                        $writeAdapter = $this->dbResource->getConnection('core_write');
                                        $query1 = "SELECT `attribute_id` FROM `{$this->dbResource
                                    ->getTableName('eav_attribute')}` "
                                            . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";
                                        $eavAttributeId = $readAdapter->fetchOne($query1);
                                        $query2 = "SELECT `value_id` FROM `{$this->dbResource
                                    ->getTableName('customer_entity_varchar')}` "
                                            ."WHERE `entity_id`='{$customerid}' AND `attribute_id`='{$eavAttributeId}'";
                                        $valueId = (int) $readAdapter->fetchOne($query2);
                                        if ($valueId <= 0) {
                                            $Query = "INSERT INTO `{$this->dbResource
                                    ->getTableName('customer_entity_varchar')}` "
                                                . "(attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerid}',"
                                                . "'{$customerProfileId}')";
                                        } else {
                                            $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` "
                                                . "SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' "
                                                . "AND `entity_id`='{$customerid}'";
                                        }
                                        $writeAdapter->query($Query);
                                        $this->prepareProfileResponse(
                                            $payment,
                                            $customerProfileId,
                                            (string)$transResponse->transId
                                        );
                                    }
                                } else { //phpcs:ignore
                                    /* print log message */
                                }

                                $names = explode(' ', $customerModel->getName());
                                $args = [
                                    'name_first' => $names[0],
                                    'name_last' => $names[1],
                                    'card_number' => $post['cc_number'],
                                    'expiration_month' => $post['expiration'],
                                    'expiration_year' => $post['expiration_yr'],
                                    'cvv' => $post['cc_cid']
                                ];
                                $this->event_manager->dispatch(
                                    'cc_added',
                                    [
                                        'cc_data' => $args,
                                        'customer' => $customerModel
                                    ]
                                );

                            } else {
                                /* save card from new request */
                                $this->prepareProfileResponse(
                                    $payment,
                                    $customerProfileId,
                                    (string)$transResponse->transId
                                );
                            }

                        }

                        $transactionResponse = $response->transactionResponse;
                        $transactionResponse->amount = $amount;
                        $card = $this->_registerCard($transactionResponse, $payment);
                        $cimToRequestMap = self::REQUEST_TYPE_AUTH_CAPTURE;
                        $payment->setAnetTransType($cimToRequestMap);
                        $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                        $card->setLastTransId((string)$transactionResponse->transId);
                        $this->_addTransaction(
                            $payment,
                            $card->getLastTransId(),
                            $newTransactionType,
                            ['is_transaction_closed' => 0],
                            [$this->_realTransactionIdKey => $card->getLastTransId()],
                            $this->cimHelper->getTransactionMessage(
                                $payment,
                                $cimToRequestMap,
                                (string)$transactionResponse->transId,
                                $card,
                                $amount
                            )
                        );

                        $card->setCapturedAmount($card->getProcessedAmount());
                        $captureTransactionId = (string)$transactionResponse->transId;
                        $card->setLastCapturedTransactionId($captureTransactionId);
                        $this->getCardsStorage()->updateCard($card);

                        $payment->setLastTransId((string)$transactionResponse->transId)
                            ->setCcTransId((string)$transactionResponse->transId)
                            ->setTransactionId((string)$transactionResponse->transId)
                            ->setIsTransactionClosed(0)
                            ->setCcAvsStatus((string)$transactionResponse->avsResultCode);
                        if ((int)$transResponse->responseCode==self::REVIEW) {
                            $payment->setIsTransactionPending(true)
                                ->setIsFraudDetected(true)
                                ->setTransactionAdditionalInfo('is_transaction_fraud', true);
                        } else {
                            $payment->setStatus(self::STATUS_APPROVED);
                        }
                        if (isset($transactionResponse->cvvResultCode)) {
                            $payment->setCcCidStatus($transactionResponse->cvvResultCode);
                        }
                    } else {
                        $exceptionMessage = (string)$transResponse->errors->error->errorText;
                        $payment->unsAdditionalInformation();
                        throw new LocalizedException(new Phrase($exceptionMessage));
                    }
                } else {
                    $transResponse = $response->transactionResponse;
                    $payment->unsAdditionalInformation();
                    $exceptionMessage = $this->cimHelper->getErrorDescription($response->messages->message->code);
                    throw new LocalizedException(new Phrase($exceptionMessage));
                }
            }
        } catch (\Exception $e) {
            $payment->unsAdditionalInformation();
            throw new LocalizedException(new Phrase(__('Gateway request error:'. $e->getMessage())));
        }
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Authorize function
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param mixed $amount
     * @return $this
     */
    public function authorize(
        \Magento\Payment\Model\InfoInterface $payment,
                                             $amount
    ) {
        $exceptionMessage = false;
        if ($amount <= 0) {
            $payment->unsAdditionalInformation();
            throw new LocalizedException(new Phrase(__('Invalid amount for authorization.')));
        }
        $this->_initCardsStorage($payment);
        if (empty($this->postData)) {
            $this->postData = $this->registry->registry('postdata');
        }
        $threedActive = $this->getThreedActive($payment);
        $post = $this->postData;
        $saveCard = $post['save_card'];
        try {
            $isMultiShipping = $this->checkoutsession->getQuote()->getData('is_multi_shipping');
            $saveFlag = 'false';
            if ((isset($post['payment_profile_id'])
                    && !empty($post['payment_profile_id'])
                    && empty($post['cc_number'])) || ($isMultiShipping == '1'
                    && !empty($paymentProfileCheck))
            ) {
                $paymentProfileCheck = $this->encryptor->decrypt($post['payment_profile_id']);
                $payment->setMdPaymentProfileId($paymentProfileCheck);

                // Here I need to set the customer_profile_id and payment_profile_id from Rev.io
                $profile_pieces = explode('::', $paymentProfileCheck);
                if (count($profile_pieces) === 4) {
                    $payment->setAdditionalInformation('md_payment_profile_id', $profile_pieces[1]);
                    $payment->setAdditionalInformation('md_customer_profile_id', $profile_pieces[0]);
                } else {
                    $payment->unsAdditionalInformation();
                    throw new LocalizedException(new Phrase('Card does not exists'));
                }

                $response = $this->cimXml
                    ->prepareAuthorizeResponse($payment, $amount, $saveFlag, $threedActive, true);
            } else {
                if ((($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != '' ||
                        ($saveCard == 'true' && isset($post['data_value'])) && $post['data_value'] != '')
                    && ($this->customerSession->getCustomerId() || ($this->cimHelper->checkAdmin()
                            && $this->objectManager->get('Magento\Backend\Model\Session\Quote')
                                ->getQuote()->getCustomerId()))) {
                    /* check customer profile id is exist or not */
                    $customerid = $payment->getOrder()->getCustomerId();
                    $customerModel = $this->customerFactory->create();
                    $customerModel->getResource()->load($customerModel, $customerid);
                    $customerProfileId = $customerModel->getMdCustomerProfileId();
                    if ($customerProfileId=='') {
                        /* save card will true only for first request by customer */
                        $saveFlag = 'true';
                    }

                }
                $response = $this->cimXml
                    ->prepareAuthorizeResponse($payment, $amount, $saveFlag, $threedActive, false);
            }

            $code = (string)$response->messages->message->code;
            $resultCode =  (string)$response->messages->resultCode;
            if ($code == 'I00001' && $resultCode == 'Ok') {
                $transResponse = $response->transactionResponse;
                if (!in_array((string)$transResponse->responseCode, [2, 3])) {
                    if (!empty($paymentProfileCheck) && empty($post['cc_number'])) {
                        $profile_pieces = explode('::', $paymentProfileCheck);
                        $payment->setCcLast4($profile_pieces[2]);
                        $payment->setCcType($profile_pieces[3]);
                        $payment->setAdditionalInformation('md_payment_profile_id', $profile_pieces[1]);
                        $payment->setMdPaymentProfileId($paymentProfileCheck);
                    } else {
                        $payment->setCcLast4(substr((string)$transResponse->accountNumber, -4, 4));
                        if (isset($this->cardArray[(string)$transResponse->accountType])):
                            $cardType = $this->cardArray[(string)$transResponse->accountType];
                        elseif (isset($post['cc_type'])):
                            $cardType = $post['cc_type'];
                        else:
                            $cardType = '';
                        endif;
                        $payment->setCcType($cardType);
                    }
                    $saveCard = $payment->getData('additional_information', 'md_save_card');
                    if ((($saveCard == 'true' && isset($post['cc_number'])) && $post['cc_number'] != '' ||
                            ($saveCard == 'true' && isset($post['data_value'])) && $post['data_value'] != '')
                        && ($this->customerSession->getCustomerId()
                            || ($this->cimHelper->checkAdmin()
                                && $this->objectManager->get('Magento\Backend\Model\Session\Quote')
                                    ->getQuote()->getCustomerId()))) {

                        if ($saveFlag=='true') {
                            /* save card from profile response */
                            $profileResponse = $response->profileResponse;
                            $resultCode =  (string)$profileResponse->messages->resultCode;
                            $code = (string)$profileResponse->messages->message->code;
                            if ($code == 'I00001' && $resultCode == 'Ok') {
                                $customerid = $this->cimHelper->checkAdmin() ?
                                    $this->objectManager->get('Magento\Backend\Model\Session\Quote')
                                        ->getQuote()->getCustomerId() : $this->customerSession->getCustomer()->getId();
                                if ($customerid=='') {
                                    $customerid = $payment->getOrder()->getCustomerId();
                                }
                                $this->saveCustomerProfileData($profileResponse, $payment, $customerid);
                            } elseif ($code == 'E00039' &&
                                strpos($profileResponse->messages->message->text, 'duplicate') !== false) {
                                $customerProfileId = preg_replace(
                                    '/[^0-9]/',
                                    '',
                                    $profileResponse->messages->message->text
                                );
                                $readAdapter = $this->dbResource->getConnection('core_read');
                                $writeAdapter = $this->dbResource->getConnection('core_write');
                                $query1 = "SELECT `attribute_id` FROM `{$this->dbResource
                                ->getTableName('eav_attribute')}` "
                                    . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";

                                $eavAttributeId = $readAdapter->fetchOne($query1);

                                $query2 = "SELECT `value_id` FROM `{$this->dbResource
                                ->getTableName('customer_entity_varchar')}` "
                                    . "WHERE `entity_id`='{$customerid}' AND `attribute_id`='{$eavAttributeId}'";
                                $valueId = (int) $readAdapter->fetchOne($query2);
                                if ($valueId <= 0) {
                                    $Query = "INSERT INTO `{$this->dbResource
                                ->getTableName('customer_entity_varchar')}` "
                                        . "(attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerid}',"
                                        . "'{$customerProfileId}')";
                                } else {
                                    $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` "
                                        . "SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' "
                                        . "AND `entity_id`='{$customerid}'";
                                }
                                $writeAdapter->query($Query);
                                $this->prepareProfileResponse($payment, $customerProfileId, (string)$transResponse->transId);

                            } elseif ($code == 'E00103') {
                                $customerProfileRes = $this->cimXml->createCustPaymentProfileFromTransaction((string)
                                $transResponse->transId);
                                $code = $customerProfileRes->messages->message->code;
                                $resultCode = $response->messages->resultCode;
                                if ($code == 'I00001' && $resultCode == 'Ok') {
                                    $customerProfileId = (string)$customerProfileRes->customerProfileId;
                                    $readAdapter = $this->dbResource->getConnection('core_read');
                                    $writeAdapter = $this->dbResource->getConnection('core_write');
                                    $query1 = "SELECT `attribute_id` FROM `{$this->dbResource
                                ->getTableName('eav_attribute')}` "
                                        . "WHERE `attribute_code`='md_customer_profile_id' LIMIT 1";
                                    $eavAttributeId = $readAdapter->fetchOne($query1);
                                    $query2 = "SELECT `value_id` FROM `{$this->dbResource
                                ->getTableName('customer_entity_varchar')}` "
                                        . "WHERE `entity_id`='{$customerid}' AND `attribute_id`='{$eavAttributeId}'";
                                    $valueId = (int) $readAdapter->fetchOne($query2);
                                    if ($valueId <= 0) {
                                        $Query = "INSERT INTO `{$this->dbResource
                                ->getTableName('customer_entity_varchar')}` "
                                            . "(attribute_id,entity_id,value) VALUES('{$eavAttributeId}','{$customerid}',"
                                            . "'{$customerProfileId}')";
                                    } else {
                                        $Query = "UPDATE `{$this->dbResource->getTableName('customer_entity_varchar')}` "
                                            . "SET `value`='{$customerProfileId}' WHERE `value_id`='{$valueId}' "
                                            . "AND `entity_id`='{$customerid}'";
                                    }
                                    $writeAdapter->query($Query);
                                    $this->prepareProfileResponse(
                                        $payment,
                                        $customerProfileId,
                                        (string)$transResponse->transId
                                    );
                                }
                            } else { //phpcs:ignore
                                /* print log message */
                            }
                            $names = explode(' ', $customerModel->getName());
                            $args = [
                                'name_first' => $names[0],
                                'name_last' => $names[1],
                                'card_number' => $post['cc_number'],
                                'expiration_month' => $post['expiration'],
                                'expiration_year' => $post['expiration_yr'],
                                'cvv' => $post['cc_cid']
                            ];
                            $this->event_manager->dispatch(
                                'cc_added',
                                [
                                    'cc_data' => $args,
                                    'customer' => $customerModel
                                ]
                            );
                        } else {
                            /* save card from new request */
                            $this->prepareProfileResponse(
                                $payment,
                                $customerProfileId,
                                (string)$transResponse->transId
                            );
                        }
                    }
                    $cimToRequestMap = self::REQUEST_TYPE_AUTH_ONLY;
                    $payment->setAnetTransType($cimToRequestMap);
                    $payment->setAmount($amount);
                    $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                    $transactionResponse = $response->transactionResponse;
                    $transactionResponse->amount = $amount;
                    $card = $this->_registerCard($transactionResponse, $payment);
                    $card->setLastTransId((string)$transactionResponse->transId);
                    $this->_addTransaction(
                        $payment,
                        $card->getLastTransId(),
                        $newTransactionType,
                        ['is_transaction_closed' => 0],
                        [$this->realTransactionIdKey => $card->getLastTransId()],
                        $this->cimHelper->getTransactionMessage(
                            $payment,
                            $cimToRequestMap,
                            (string)$transactionResponse->getTransId,
                            $card,
                            $amount
                        )
                    );

                    $payment->setLastTransId((string)$transactionResponse->transId)
                        ->setCcTransId((string)$transactionResponse->transId)
                        ->setTransactionId((string)$transactionResponse->transId)
                        ->setIsTransactionClosed(0)
                        ->setCcAvsStatus((string)$transactionResponse->avsResultCode);
                    if ((int)$transResponse->responseCode==self::REVIEW) {
                        $payment->setIsTransactionPending(true)
                            ->setIsFraudDetected(true)
                            ->setTransactionAdditionalInfo('is_transaction_fraud', true);
                    } else {
                        $payment->setStatus(self::STATUS_APPROVED);
                    }

                    /*
                    * checking if we have cvCode in response bc
                    * if we don't send cvn we don't get cvCode in response
                    */
                    if (isset($transactionResponse->cvvResultCode)) {
                        $payment->setCcCidStatus($transactionResponse->cvvResultCode);
                    }
                } else {
                    $exceptionMessage = (string)$transResponse->errors->error->errorText;
                    $payment->unsAdditionalInformation();
                    throw new LocalizedException(new Phrase($exceptionMessage));
                }
            } else {
                $exceptionMessage = $this->cimHelper->getErrorDescription($response->messages->message->code);
                $payment->unsAdditionalInformation();
                throw new LocalizedException(new Phrase($exceptionMessage));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(new Phrase('Authorize.net Cim Gateway request error: '. $e->getMessage()));
        }
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    // @codingStandardsIgnoreEnd
    /**
     * Get Threed Active
     *
     * @param mixed $payment
     * @return bool
     */
    protected function getThreedActive($payment)
    {
        $isCentinelActive = $this->cimConfig->getCardinalThreedActive();
        if ($isCentinelActive && $this->isCountryAvailable($payment)) {
            return true;
        }
        return false;
    }

    /**
     * Check country available
     *
     * @param mixed $payment
     * @return bool
     */
    private function isCountryAvailable($payment)
    {
        $order = $payment->getOrder();
        $specifictCountries = $this->cimConfig->get3DSecureSpecificCountries();
        $billingAddress = $order->getBillingAddress();
        if (!empty($specifictCountries) && !in_array($billingAddress->getCountryId(), $specifictCountries)) {
            return false;
        }
        return true;
    }
}
