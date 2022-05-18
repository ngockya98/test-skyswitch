<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Observer;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class CheckoutSubmitBefore implements ObserverInterface
{
    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    public function __construct(
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->companyRepository = $companyRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var Quote $quote
         */
        $quote = $observer->getEvent()->getQuote();
        if ($quote->getCustomer()->getExtensionAttributes()
            && $quote->getCustomer()->getExtensionAttributes()->getAmCompanyAttributes()
        ) {
            $companyId = (int)$quote->getCustomer()->getExtensionAttributes()->getAmCompanyAttributes()->getCompanyId();
            try {

                $company = $this->companyRepository->getById($companyId);
                if ($company->getCompanyId() && !$company->isActive()) {
                    throw new LocalizedException(__('You do not have permission to place an order.'));
                }
            } catch (NoSuchEntityException $e) {
                return $this;
            }
        }
    }
}
