<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Company\Form;

use Amasty\CompanyAccount\Model\Backend\Company\Registry as CompanyRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;

class CreditCurrency extends Field
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var CompanyRegistry
     */
    private $companyRegistry;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        CompanyRegistry $companyRegistry,
        CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->companyRegistry = $companyRegistry;
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
        $config['value'] = $this->storeManager->getWebsite()->getBaseCurrencyCode();

        $companyCreditCurrencyCode = $this->getCompanyCreditCurrency();
        $configOptions = (!empty($config['options'])) ? $config['options'] : [];

        if ($companyCreditCurrencyCode
            && !$this->isOptionsContainCurrencyCode($configOptions, $companyCreditCurrencyCode)
        ) {
            $currencyData = $this->getCurrencyData($companyCreditCurrencyCode);

            if ($currencyData) {
                $config['options'][] = $currencyData;
            }
        }

        $this->setData('config', $config);
    }

    private function getCompanyCreditCurrency(): ?string
    {
        $company = $this->companyRegistry->get();
        $credit = $company->getExtensionAttributes()->getCredit();
        return $credit ? $credit->getCurrencyCode() : null;
    }

    /**
     * Get currency data by currency code.
     *
     * @param string $currencyCode
     * @return array
     */
    private function getCurrencyData(string $currencyCode): array
    {
        $currencyData = [];

        if ($currencyCode) {
            $currencyName = $this->localeCurrency->getCurrency($currencyCode)->getName();

            if ($currencyName) {
                $currencyData = [
                    'value' => $currencyCode,
                    'label' => $currencyName
                ];
            }
        }

        return $currencyData;
    }

    /**
     * Is config currency contain currency code.
     *
     * @param array $configOptions
     * @param string $currencyCode
     * @return bool
     */
    private function isOptionsContainCurrencyCode(array $configOptions, string $currencyCode): bool
    {
        foreach ($configOptions as $option) {
            if (isset($option['value']) && $option['value'] == $currencyCode) {
                return true;
            }
        }

        return false;
    }
}
