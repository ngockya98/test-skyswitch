<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Format
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    public function execute(float $price, ?string $currency): string
    {
        return $this->priceCurrency->format(
            $price,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $currency
        );
    }
}
