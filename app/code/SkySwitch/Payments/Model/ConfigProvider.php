<?php

namespace SkySwitch\Payments\Model;

use Magedelight\Authorizecim\Model\ConfigProvider as MageConfigProvider;
use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Magedelight\Authorizecim\Model\Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlInterface;
use Magedelight\Authorizecim\Helper\Data;
use Magento\Backend\Model\Session\Quote;
use Magedelight\Authorizecim\Model\Api\Xml;
use Magento\Payment\Model\Config as PaymentConfig;
use Magedelight\Authorizecim\Model\CardsFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Revio\Service\Revio;
use Magento\Framework\App\DeploymentConfig;
use Magento\Customer\Api\CustomerRepositoryInterface;

class ConfigProvider extends MageConfigProvider
{
    /**
     * @var Revio
     */
    protected $revio_service;

    /**
     * @var DeploymentConfig
     */
    protected $deployment_config;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer_repository;

    /**
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param CustomerFactory $customerFactory
     * @param UrlInterface $urlBuilder
     * @param Data $dataHelper
     * @param Quote $sessionQuote
     * @param Xml $cimXml
     * @param PaymentConfig $paymentConfig
     * @param CardsFactory $cardFactory
     * @param EncryptorInterface $encryptor
     * @param Revio $revio_service
     * @param DeploymentConfig $deployment_config
     * @param CustomerRepositoryInterface $customer_repository
     * @param array $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        Config $config,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        UrlInterface $urlBuilder,
        Data $dataHelper,
        Quote $sessionQuote,
        Xml $cimXml,
        PaymentConfig $paymentConfig,
        CardsFactory $cardFactory,
        EncryptorInterface $encryptor,
        Revio $revio_service,
        DeploymentConfig $deployment_config,
        CustomerRepositoryInterface $customer_repository,
        array $methodCodes = []
    ) {
        $this->deployment_config = $deployment_config;
        $this->revio_service = new Revio($this->deployment_config->get('services/revio'));
        $this->customer_repository = $customer_repository;

        parent::__construct(
            $ccConfig,
            $paymentHelper,
            $config,
            $checkoutSession,
            $customerSession,
            $customerFactory,
            $urlBuilder,
            $dataHelper,
            $sessionQuote,
            $cimXml,
            $paymentConfig,
            $cardFactory,
            $encryptor,
            $methodCodes
        );
    }

    /**
     * Get Stored Cards
     *
     * @return array
     */
    public function getStoredCards()
    {
        $result = [];
        $card_data = [];
        $website_id = $this->dataHelper->getWebsiteId();
        $customer_account_scope =  $this->dataHelper->getCustomerAccountScope();

        if ($this->dataHelper->checkAdmin()) {
            $customer_id = $this->sessionQuote->getQuote()->getCustomerId();
            $customer = $this->customerFactory->create();
            $customer->getResource()->load($customer, $customer_id); //phpcs:ignore
        } else {
            $customer = $this->customerSession->getCustomer();
            $customer_id = $customer->getId();
        }

        $customer_data = $this->customer_repository->getById($customer_id);
        $reseller_id = $customer_data->getExtensionAttributes()->getResellerId();

        if (empty($reseller_id)) {
            $this->logger->error(
                'Unable to retrieve Payment methods from Rev.io. Customer '
                . $customer_id
                . ' does not have a reseller id.'
            );
            $result['new'] = 'Use other card';
            return $result;
        }

        $card_model = $this->cardFactory->create();
        $card_data = $card_model->getCollection()
            ->addFieldToFilter('customer_id', $customer_id);

        if ($customer_account_scope) {
            $card_data->addFieldToFilter('website_id', $website_id);
        }

        $card_data->getData();

        $payment_accounts = $this->revio_service->getResellerPaymentAccounts($reseller_id);

        foreach ($payment_accounts['records'] as $key => $payment_account) {
            if ($payment_account['method'] !== strtoupper(Revio::CC)) {
                continue;
            }

            if (empty($payment_account['token'])) {
                if (empty($customer_id)) {
                    continue;
                }

                foreach ($card_data as $key => $card) {
                    if ($card['cc_last_4'] == $payment_account['last_4']
                        && $card['firstname'] == $payment_account['credit_card']['name_first']
                        && $card['lastname'] == $payment_account['credit_card']['name_last']
                    ) {
                        $payment_account['token'] = $card['customer_profile_id'] . '::' . $card['payment_profile_id'];
                        break;
                    }
                }

                if (empty($payment_account['token'])) {
                    continue;
                }
            }

            $key = $payment_account['token']
                . '::' . $payment_account['last_4'] . '::'
                . ($payment_account['credit_card']['brand'] ?? 'VISA');

            $card_replaced = 'XXXX-'.$payment_account['last_4'];
            $result[$this->encryptor->encrypt((string)$key)] = sprintf(
                '%s, %s %s',
                $card_replaced,
                $payment_account['credit_card']['name_first'],
                $payment_account['credit_card']['name_last']
            );
        }

        $result['new'] = 'Use other card';

        return $result;
    }
}
