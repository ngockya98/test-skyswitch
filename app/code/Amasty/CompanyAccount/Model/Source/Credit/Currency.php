<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Credit;

use Amasty\CompanyAccount\Model\WebsiteCurrency;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\ResolverInterface;

class Currency implements OptionSourceInterface
{
    public const CURRENCY_LABEL_KEY = 1;

    /**
     * @var array
     */
    private $options;

    /**
     * @var WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var CurrencyBundle
     */
    private $currencyBundle;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    public function __construct(
        WebsiteCurrency $websiteCurrency,
        CurrencyBundle $currencyBundle,
        ResolverInterface $localeResolver
    ) {
        $this->websiteCurrency = $websiteCurrency;
        $this->currencyBundle = $currencyBundle;
        $this->localeResolver = $localeResolver;
    }

    /**
     * To option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        if ($this->options === null) {
            $options = $this->currencyBundle->get($this->localeResolver->getLocale())['Currencies'];
        }

        $this->options = [];
        $allowedCurrencies = $this->websiteCurrency->getAllowedCreditCurrencies();

        foreach ($options as $code => $option) {
            if (!isset($allowedCurrencies[$code])) {
                continue;
            }

            $creditCurrency = $option[self::CURRENCY_LABEL_KEY];
            $this->options[] = ['label' => $creditCurrency, 'value' => $code];
        }

        return $this->options;
    }
}
