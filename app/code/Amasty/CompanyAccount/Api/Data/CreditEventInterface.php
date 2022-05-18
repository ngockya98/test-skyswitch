<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Api\Data;

interface CreditEventInterface
{
    public const MAIN_TABLE = 'amasty_company_credit_event';

    public const ID = 'id';
    public const CREDIT_ID = 'credit_id';
    public const USER_ID = 'user_id';
    public const USER_TYPE = 'user_type';
    public const CURRENCY_CREDIT = 'currency_credit';
    public const CURRENCY_EVENT = 'currency_event';
    public const TYPE = 'type';
    public const AMOUNT = 'amount';
    public const RATE = 'rate';
    public const RATE_CREDIT = 'rate_credit';
    public const BALANCE = 'balance';
    public const DATE = 'datetime';
    public const COMMENT = 'comment';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int|null $id
     * @return void
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * @param int|null $userId
     * @return void
     */
    public function setUserId(?int $userId): void;

    /**
     * @return int|null
     */
    public function getCreditId(): ?int;

    /**
     * @param int $creditId
     * @return void
     */
    public function setCreditId(int $creditId): void;

    /**
     * @return string|null
     */
    public function getCurrencyCredit(): ?string;

    /**
     * @param string $currencyCredit
     * @return void
     */
    public function setCurrencyCredit(string $currencyCredit): void;

    /**
     * @return string|null
     */
    public function getCurrencyEvent(): ?string;

    /**
     * @param string $currencyEvent
     * @return void
     */
    public function setCurrencyEvent(string $currencyEvent): void;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void;

    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @param float $amount
     * @return void
     */
    public function setAmount(float $amount): void;

    /**
     * @return float
     */
    public function getRate(): float;

    /**
     * @param float $rate
     * @return void
     */
    public function setRate(float $rate): void;

    /**
     * @return float
     */
    public function getCreditRate(): float;

    /**
     * @param float $creditRate
     * @return void
     */
    public function setCreditRate(float $creditRate): void;

    /**
     * @return float
     */
    public function getBalance(): float;

    /**
     * @param float $balance
     * @return void
     */
    public function setBalance(float $balance): void;

    /**
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * @param string|null $comment
     * @return void
     */
    public function setComment(?string $comment): void;

    /**
     * @return string|null
     */
    public function getDate(): ?string;

    /**
     * @param string|null $timestamp
     * @return void
     */
    public function setDate(?string $timestamp): void;

    /**
     * Update amount from model with current event type.
     *
     * @return void
     */
    public function updateAmount(): void;
}
