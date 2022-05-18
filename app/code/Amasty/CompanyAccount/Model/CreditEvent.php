<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent as CreditEventResource;
use Amasty\CompanyAccount\Model\Source\Credit\Operation;
use Magento\Framework\Model\AbstractModel;

class CreditEvent extends AbstractModel implements CreditEventInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(CreditEventResource::class);
    }

    public function updateAmount(): void
    {
        $amount = abs($this->getAmount());
        switch ($this->getType()) {
            case Operation::MINUS_BY_ADMIN:
            case Operation::OVERDRAFT_PENALTY:
            case Operation::PLACE_ORDER:
                $amount = -1 * $amount;
                break;
            case Operation::PLUS_BY_ADMIN:
            case Operation::PLUS_BY_COMPANY:
            case Operation::CANCEL_ORDER:
            case Operation::REFUND_ORDER:
                $amount = 1 * $amount;
                break;
        }
        $this->setAmount($amount);
    }

    public function getUserId(): ?int
    {
        return $this->hasData(CreditEventInterface::USER_ID)
            ? (int) $this->_getData(CreditEventInterface::USER_ID)
            : null;
    }

    public function setUserId(?int $userId): void
    {
        $this->setData(CreditEventInterface::USER_ID, $userId);
    }

    public function getCreditId(): ?int
    {
        return $this->hasData(CreditEventInterface::CREDIT_ID)
            ? (int) $this->_getData(CreditEventInterface::CREDIT_ID)
            : null;
    }

    public function setCreditId(int $creditId): void
    {
        $this->setData(CreditEventInterface::CREDIT_ID, $creditId);
    }

    public function getCurrencyCredit(): ?string
    {
        return $this->_getData(CreditEventInterface::CURRENCY_CREDIT);
    }

    public function setCurrencyCredit(string $currencyCredit): void
    {
        $this->setData(CreditEventInterface::CURRENCY_CREDIT, $currencyCredit);
    }

    public function getCurrencyEvent(): ?string
    {
        return $this->_getData(CreditEventInterface::CURRENCY_EVENT);
    }

    public function setCurrencyEvent(string $currencyEvent): void
    {
        $this->setData(CreditEventInterface::CURRENCY_EVENT, $currencyEvent);
    }

    public function getType(): ?string
    {
        return $this->_getData(CreditEventInterface::TYPE);
    }

    public function setType(string $type): void
    {
        $this->setData(CreditEventInterface::TYPE, $type);
    }

    public function getAmount(): float
    {
        return (float) $this->_getData(CreditEventInterface::AMOUNT);
    }

    public function setAmount(float $amount): void
    {
        $this->setData(CreditEventInterface::AMOUNT, $amount);
    }

    public function getRate(): float
    {
        return (float) $this->_getData(CreditEventInterface::RATE);
    }

    public function setRate(float $rate): void
    {
        $this->setData(CreditEventInterface::RATE, $rate);
    }

    public function getCreditRate(): float
    {
        return (float) $this->_getData(CreditEventInterface::RATE_CREDIT);
    }

    public function setCreditRate(float $creditRate): void
    {
        $this->setData(CreditEventInterface::RATE_CREDIT, $creditRate);
    }

    public function getBalance(): float
    {
        return (float) $this->_getData(CreditEventInterface::BALANCE);
    }

    public function setBalance(float $balance): void
    {
        $this->setData(CreditEventInterface::BALANCE, $balance);
    }

    public function getComment(): ?string
    {
        return $this->_getData(CreditEventInterface::COMMENT);
    }

    public function setComment(?string $comment): void
    {
        $this->setData(CreditEventInterface::COMMENT, $comment);
    }

    public function getDate(): ?string
    {
        return $this->_getData(CreditEventInterface::DATE);
    }

    public function setDate(?string $timestamp): void
    {
        $this->setData(CreditEventInterface::DATE, $timestamp);
    }
}
