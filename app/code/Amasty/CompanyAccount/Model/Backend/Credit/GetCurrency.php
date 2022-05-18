<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Backend\Credit;

use Amasty\CompanyAccount\Model\Backend\Company\Registry as CompanyRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class GetCurrency
{
    /**
     * @var CompanyRegistry
     */
    private $companyRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CompanyRegistry $companyRegistry,
        StoreManagerInterface $storeManager
    ) {
        $this->companyRegistry = $companyRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve current currency for current editable credit.
     * Editable credit retrieve from current company.
     * CompanyRegistry must return current editable company.
     * @see \Amasty\CompanyAccount\Model\Backend\Company\Registry
     *
     * @return string
     * @throws LocalizedException
     */
    public function execute(): string
    {
        $currencyCode = null;

        $credit = $this->companyRegistry->get()->getExtensionAttributes()->getCredit();
        if ($credit) {
            $currencyCode = $credit->getCurrencyCode();
        }

        if (!$currencyCode) {
            try {
                $currencyCode = $this->storeManager->getWebsite()->getBaseCurrencyCode();
            } catch (LocalizedException $e) {
                throw new LocalizedException(__('Can\'t retrieve credit currency.'));
            }
        }

        return $currencyCode;
    }
}
