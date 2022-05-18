<?php

namespace SkySwitch\Payments\Observer;

use Revio\Service\Revio;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class SaveRevioCc implements ObserverInterface
{
    /**
     * @var DeploymentConfig
     */
    protected $deployment_config;

    /**
     * @var Revio
     */
    protected $revio_service;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer_repository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DeploymentConfig $deployment_config
     * @param CustomerRepositoryInterface $customer_repository
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeploymentConfig $deployment_config,
        CustomerRepositoryInterface $customer_repository,
        LoggerInterface $logger
    ) {
        $this->deployment_config = $deployment_config;
        $this->revio_service = new Revio($deployment_config->get('services/revio'));
        $this->customer_repository = $customer_repository;
        $this->logger = $logger;
    }

    /**
     * Execute event method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $observer->getData('cc_data');
        $customer = $observer->getData('customer');
        $customer_data = $this->customer_repository->getById($customer->getId());
        $reseller_id = $customer_data->getExtensionAttributes()->getResellerId();
        if (empty($reseller_id)) {
            $this->logger->error(
                'Unable to add Payment method to Rev.io. Customer '
                . $customer->getId()
                . ' does not have a reseller id.'
            );
            return;
        }

        $this->revio_service->createPaymentAccount($params, Revio::CC, $reseller_id);
    }
}
