<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Order;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Price\Convert as PriceConvert;
use Magento\Sales\Api\Data\OrderInterface;

class CanPlace
{
    /**
     * @var PriceConvert
     */
    private $priceConvert;

    public function __construct(PriceConvert $priceConvert)
    {
        $this->priceConvert = $priceConvert;
    }

    public function execute(OrderInterface $order, CreditInterface $credit): bool
    {
        $balance = $credit->getBalance();
        $grandTotal = $order->getBaseGrandTotal();
        if ($order->getBaseCurrencyCode() != $credit->getCurrencyCode()) {
            $grandTotal = $this->priceConvert->execute(
                $grandTotal,
                $order->getBaseCurrencyCode(),
                $credit->getCurrencyCode()
            );
        }
        if ($credit->isOverdraftAllowed()) {
            $balance += $credit->getOverdraftLimit();
        }

        return $grandTotal <= $balance;
    }
}
