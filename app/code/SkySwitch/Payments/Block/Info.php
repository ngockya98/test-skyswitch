<?php

namespace SkySwitch\Payments\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magedelight\Authorizecim\Model\Config as MageConfig;
use Magento\Store\Model\StoreManager;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magedelight\Authorizecim\Helper\Data;
use Magento\Framework\Pricing\Helper\Data as MagentoData;
use Magedelight\Authorizecim\Model\Payment\Cards;

class Info extends \Magedelight\Authorizecim\Block\Info
{
    /**
     * @param Registry $registry
     * @param Context $context
     * @param Config $paymentConfig
     * @param MageConfig $cimConfig
     * @param StoreManager $storeManager
     * @param Transaction $payment
     * @param Data $cimHelper
     * @param MagentoData $currencyHelper
     * @param Cards $cardpayment
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        Context $context,
        Config $paymentConfig,
        MageConfig $cimConfig,
        StoreManager $storeManager,
        Transaction $payment,
        Data $cimHelper,
        MagentoData $currencyHelper,
        Cards $cardpayment,
        array $data = []
    ) {
        $this->core_registry = $registry;
        parent::__construct(
            $context,
            $paymentConfig,
            $cimConfig,
            $storeManager,
            $payment,
            $cimHelper,
            $currencyHelper,
            $cardpayment,
            $data
        );
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->core_registry->registry('current_order');
    }

    /**
     * Get Cards info
     *
     * @return array
     */
    public function getCards()
    {
        $this->cardpayment->setPayment($this->getInfo());
        $cardsData = $this->cardpayment->getCards();
        $cards = [];
        if (is_array($cardsData)) {
            foreach ($cardsData as $cardInfo) {
                $data = [];

                $lastTransactionId = $this->getData('info')->getData('cc_trans_id');
                $cardTransactionId = $cardInfo->getTransactionId();
                if ($lastTransactionId == $cardTransactionId) {
                    if ($cardInfo->getProcessedAmount()) {
                        $amount = $this->currencyHelper->currency($this->getOrder()->getGrandTotal(), true, false);
                        $data['Processed Amount'] = $amount;
                    }
                    if ($cardInfo->getBalanceOnCard() && is_numeric($cardInfo->getBalanceOnCard())) {
                        $balance = $this->currencyHelper->currency($cardInfo->getBalanceOnCard(), true, false);
                        $data['Remaining Balance'] = $balance;
                    }
                    if ($this->cimHelper->checkAdmin()) {
                        if ($cardInfo->getApprovalCode() && is_string($cardInfo->getApprovalCode())) {
                            $data['Approval Code'] = $cardInfo->getApprovalCode();
                        }
                        if ($cardInfo->getMethod() && is_numeric($cardInfo->getMethod())) {
                            $data['Method'] = ($cardInfo->getMethod() == 'CC') ? __('Credit Card') : __('eCheck');
                        }
                        if ($cardInfo->getLastTransId() && $cardInfo->getLastTransId()) {
                            $data['Transaction Id'] = str_replace(
                                ['-capture', '-void', '-refund'],
                                '',
                                $cardInfo->getLastTransId()
                            );
                        }
                        if ($cardInfo->getAvsResultCode() && is_string($cardInfo->getAvsResultCode())) {
                            $data['AVS Response'] = $this->cimHelper->getAvsLabel($cardInfo->getAvsResultCode());
                        }
                        if ($cardInfo->getCVNResultCode() && is_string($cardInfo->getCVNResultCode())) {
                            $data['CVN Response'] = $this->cimHelper->getCvnLabel($cardInfo->getCVNResultCode());
                        }
                        if ($cardInfo->getCardCodeResponseCode() && is_string($cardInfo->getreconciliationID())) {
                            $data['CCV Response'] = $cardInfo->getCardCodeResponseCode();
                        }
                        if ($cardInfo->getMerchantReferenceCode() &&
                            is_string($cardInfo->getMerchantReferenceCode())) {
                            $data['Merchant Reference Code'] = $cardInfo->getMerchantReferenceCode();
                        }
                        if ($cardInfo->getCAVVResponseCode() && is_string($cardInfo->getCAVVResponseCode())) {
                            $data['CAVV Response'] = $this->cimHelper->getCavvLabel($cardInfo->getCAVVResponseCode());
                        }
                        if ($cardInfo->getFdsaction() && is_string($cardInfo->getFdsaction())) {
                            $data['FDS Action'] = $cardInfo->getFdsaction();
                        }
                        if ($cardInfo->getAfdinfo()) {
                            // $data['AFD Response'] = $cardInfo->getAfdinfo();
                            $data['FDS Response'] = base64_decode($cardInfo->getAfdinfo()); //phpcs:ignore
                        }
                    }
                    $this->setCardInfoObject($cardInfo);
                    $cards[] = array_merge($this->getSpecificInformation(), $data); //phpcs:ignore
                    $this->unsCardInfoObject();
                    $this->_paymentSpecificInformation = null;
                }
            }
        }
        if ($this->getInfo()->getCcType() && $this->_isCheckoutProgressBlockFlag && count($cards) == 0) {
            $cards[] = $this->getSpecificInformation();
        }
        return $cards;
    }
}
