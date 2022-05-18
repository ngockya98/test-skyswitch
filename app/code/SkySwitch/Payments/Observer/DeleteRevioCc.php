<?php

namespace SkySwitch\Payments\Observer;

use Revio\Service\Revio;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Event\ObserverInterface;
use Revio\Service\Exception\BadRequestException;
use Psr\Log\LoggerInterface;
use Revio\Service\Exception\NotFoundException;
use Magento\Customer\Api\CustomerRepositoryInterface;

class DeleteRevioCc implements ObserverInterface
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer_repository;

    /**
     * @param DeploymentConfig $deployment_config
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customer_repository
     */
    public function __construct(
        DeploymentConfig $deployment_config,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customer_repository
    ) {
        $this->deployment_config = $deployment_config;
        $this->revio_service = new Revio($deployment_config->get('services/revio'));
        $this->logger = $logger;
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
        $params = $observer->getData('cc_data');
        $customer = $observer->getData('customer');
        $customer_data = $this->customer_repository->getById($customer->getId());
        $reseller_id = $customer_data->getExtensionAttributes()->getResellerId();

        if (empty($reseller_id)) {
            $this->logger->error(
                'Unable to delete Payment method from Rev.io. Customer '
                . $customer->getId()
                . ' does not have a reseller id.'
            );
            return;
        }

        try {
            $this->revio_service->deletePaymentAccount(
                $params['cc_last_4'],
                $params['firstname'],
                $params['lastname'],
                $reseller_id
            );
        } catch (BadRequestException $e) {
            $this->logger->error(__('Credit card not removed from Rev.io. %s', $e->getMessage()));
        } catch (NotFoundException $e) {
            $this->logger->error(__('Credit card not found in Rev.io. %s', $e->getMessage()));
        }
    }
}
