<?php

namespace SkySwitch\Payments\Observer;

use Revio\Service\Revio;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class UpdateRevioCc implements ObserverInterface
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
     * @param DeploymentConfig $deployment_config
     * @param CustomerRepositoryInterface $customer_repository
     */
    public function __construct(
        DeploymentConfig $deployment_config,
        CustomerRepositoryInterface $customer_repository
    ) {
        $this->deployment_config = $deployment_config;
        $this->revio_service = new Revio($deployment_config->get('services/revio'));
        $this->customer_repository = $customer_repository;
    }

    /**
     * Execute event method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $old_data = $observer->getData('old_card_data');
        $new_data = $observer->getData('new_card_data');
        $customer = $observer->getData('customer');
        $customer_data = $this->customer_repository->getById($customer->getId());
        $reseller_id = $customer_data->getExtensionAttributes()->getResellerId();

        if (empty($reseller_id)) {
            $this->logger->error(
                'Unable to update Payment method in Rev.io. Customer '
                . $customer->getId()
                . ' does not have a reseller id.'
            );
            return;
        }

        $this->revio_service->updatePaymentAccount($old_data, $new_data, $reseller_id);
    }
}
