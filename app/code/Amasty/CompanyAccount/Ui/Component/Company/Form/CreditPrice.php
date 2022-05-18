<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Company\Form;

use Amasty\CompanyAccount\Model\Backend\Credit\GetCurrency as GetCreditCurrency;
use Amasty\CompanyAccount\Model\WebsiteCurrency;
use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\Currency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;
use Zend_Currency_Exception;

class CreditPrice extends Field
{
    /**
     * @var WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var Currencysymbol
     */
    private $currencySymbol;

    /**
     * @var GetCreditCurrency
     */
    private $getCreditCurrency;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    public function __construct(
        GetCreditCurrency $getCreditCurrency,
        WebsiteCurrency $websiteCurrency,
        Currencysymbol $currencySymbol,
        CurrencyInterface $localeCurrency,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->websiteCurrency = $websiteCurrency;
        $this->currencySymbol = $currencySymbol;
        $this->getCreditCurrency = $getCreditCurrency;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * Prepare component configuration.
     *
     * @return void
     * @throws LocalizedException
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');
        $config = $this->updateCurrencyConfig($config);
        $this->setData('config', $config);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     * @throws Zend_Currency_Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        $name = $this->getData('name');
        if (isset($dataSource['data']['store_credit'][$name])) {
            $dataSource['data']['store_credit'][$name] = $this->formatPriceForInput(
                (float) $dataSource['data']['store_credit'][$name]
            );
        }
        return parent::prepareDataSource($dataSource);
    }

    /**
     * @param array $data
     * @return array
     * @throws LocalizedException
     */
    private function updateCurrencyConfig(array $data): array
    {
        $currencyCode = $this->getCreditCurrency->execute();
        $currencySymbols = $this->getCurrencySymbols();
        $data['addbefore'] = $currencySymbols[$currencyCode] ?? '';
        $data['currencySymbols'] = $currencySymbols;

        return $data;
    }

    private function getCurrencySymbols(): array
    {
        $symbols = [];

        $symbolsData = $this->currencySymbol->getCurrencySymbolsData();
        foreach ($this->websiteCurrency->getAllowedCreditCurrencies() as $currencyCode) {
            $symbols[$currencyCode] = $symbolsData[$currencyCode]['displaySymbol'] ?? null;
        }

        return $symbols;
    }

    /**
     * @param float $price
     * @return string
     * @throws LocalizedException
     * @throws Zend_Currency_Exception
     */
    private function formatPriceForInput(float $price): string
    {
        $creditCurrency = $this->localeCurrency->getCurrency($this->getCreditCurrency->execute());
        return $creditCurrency->toCurrency($price, ['display' => Currency::NO_SYMBOL]);
    }
}
