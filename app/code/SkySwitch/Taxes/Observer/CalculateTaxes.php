<?php

namespace SkySwitch\Taxes\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use SkySwitch\Taxes\TaxManager;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Backend\Customer\Interceptor;

class CalculateTaxes implements ObserverInterface
{
    /**
     * @var TaxManager
     */
    protected $tax_manager;

    /**
     * @var Session
     */
    protected $customer_session;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer_repository;

    /**
     * @param TaxManager $tax_manager
     * @param Session $customer_session
     * @param CustomerRepositoryInterface $customer_repository
     */
    public function __construct(
        TaxManager $tax_manager,
        Session $customer_session,
        CustomerRepositoryInterface $customer_repository
    ) {
        $this->tax_manager = $tax_manager;
        $this->customer_session = $customer_session;
        $this->customer_repository = $customer_repository;
    }

    /**
     * Main observer function
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $total = $observer->getData('total');
        $quote = $observer->getQuote();
        $customer = $this->customer_session->getCustomer();
        if (is_a($customer, Interceptor::class)) {
            return $this;
        }
        $customer_data = $this->customer_repository->getById($customer->getId());
        $reseller_id = $customer_data->getExtensionAttributes()->getResellerId();

        if (empty($reseller_id)) {
            $this->logger->error('Unable to calculate CSI taxes. Customer '
                . $customer->getId() . ' does not have a reseller id.');
            return $this;
        }

        $total_taxes = $this->tax_manager->calculateTaxes($quote->getAllItems(), $reseller_id);

        $total->setTotalAmount('tax', $total_taxes);

        $total->setBaseTotalAmount('tax', $total_taxes);
        $total->setGrandTotal((float)$total->getGrandTotal() + $total_taxes);
        $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $total_taxes);

        return $this;
    }
}
