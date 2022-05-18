<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\CreditEvent\Listing\Column;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class EventAmount extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName])) {
                    $amount = $this->convert((float) $item[$fieldName], (float) $item[CreditEventInterface::RATE]);
                    $item[$fieldName] = $this->formatPrice(
                        $amount,
                        $item[CreditEventInterface::CURRENCY_EVENT] ?? null
                    );
                }
            }
        }

        return $dataSource;
    }

    private function convert(float $price, float $rate)
    {
        if ($rate) {
            $price *= $rate;
        }

        return $price;
    }

    private function formatPrice(float $amount, ?string $currency): string
    {
        return $this->priceCurrency->format(
            $amount,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $currency
        );
    }
}
