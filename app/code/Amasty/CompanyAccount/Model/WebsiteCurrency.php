<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\PriceCurrency;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteCurrency
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array|null
     */
    private $baseCurrencies;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var Currency[]
     */
    private $currencies;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        PriceCurrency $priceCurrency
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->priceCurrency = $priceCurrency;
    }

    public function isCreditCurrencyEnabled(string $currencyCode): bool
    {
        $baseCurrencies = $this->getAllowedCreditCurrencies();

        return isset($baseCurrencies[$currencyCode]);
    }

    public function getAllowedCreditCurrencies(): array
    {
        if ($this->baseCurrencies === null) {
            $this->baseCurrencies = [];
            foreach ($this->storeManager->getWebsites(true) as $website) {
                $currency = $website->getBaseCurrencyCode();
                $this->baseCurrencies[$currency] = $currency;
            }
        }

        return $this->baseCurrencies;
    }

    public function getCurrencyByCode(?string $currencyCode = null): Currency
    {
        if (isset($this->currencies[$currencyCode])) {
            return $this->currencies[$currencyCode];
        }

        if (!$currencyCode) {
            return $this->storeManager->getStore()->getBaseCurrency();
        }

        $currency = $this->currencyFactory->create();
        $this->currencies[$currencyCode] = $currency->load($currencyCode);

        return $this->currencies[$currencyCode];
    }

    public function isRateEnabled(string $fromCurrency, string $toCurrency): bool
    {
        $isEnabled = false;

        if ($fromCurrency == $toCurrency) {
            $isEnabled = true;
        } elseif ($toCurrency !== null) {
            $rate = $this->getBaseRate($fromCurrency, $toCurrency);

            if ($rate) {
                $isEnabled = true;
            }
        }

        return $isEnabled;
    }

    public function getBaseRate(string $fromCurrencyCode, string $toCurrencyCode): float
    {
        $toCurrency = $this->getCurrencyByCode($toCurrencyCode);
        $fromCurrency = $this->getCurrencyByCode($fromCurrencyCode);

        return (float) $this->priceCurrency->getCurrency(null, $fromCurrency)->getRate($toCurrency);
    }
}
