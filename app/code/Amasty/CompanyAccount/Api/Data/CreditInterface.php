<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Api\Data;

interface CreditInterface
{
    public const MAIN_TABLE = 'amasty_company_credit';

    public const ID = 'id';
    public const COMPANY_ID = 'company_id';
    public const BALANCE = 'balance';
    public const ISSUED_CREDIT = 'credit';
    public const BE_PAID = 'be_paid';
    public const CURRENCY_CODE = 'currency_code';
    public const ALLOW_OVERDRAFT = 'allow_overdraft';
    public const OVERDRAFT_LIMIT = 'overdraft_limit';
    public const OVERDRAFT_REPAY_PERIOD = 'overdraft_repay_period';
    public const OVERDRAFT_REPAY_DIGIT = 'overdraft_repay_digit';
    public const OVERDRAFT_REPAY_TYPE = 'overdraft_repay_type';
    public const OVERDRAFT_PENALTY = 'overdraft_penalty';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \Amasty\CompanyAccount\Api\Data\CreditInterface
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getCompanyId(): ?int;

    /**
     * @param int $companyId
     * @return void
     */
    public function setCompanyId(int $companyId): void;

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
     * @return float
     */
    public function getIssuedCredit(): float;

    /**
     * @param float $issuedCredit
     * @return void
     */
    public function setIssuedCredit(float $issuedCredit): void;

    /**
     * @return float
     */
    public function getBePaid(): float;

    /**
     * @param float $bePaid
     * @return void
     */
    public function setBePaid(float $bePaid): void;

    /**
     * @return string|null
     */
    public function getCurrencyCode(): ?string;

    /**
     * @param string $currencyCode
     * @return void
     */
    public function setCurrencyCode(string $currencyCode): void;

    /**
     * @return bool
     */
    public function isOverdraftAllowed(): bool;

    /**
     * @param bool $overdraftAllowed
     * @return void
     */
    public function setOverdraftAllowed(bool $overdraftAllowed): void;

    /**
     * @return float|null
     */
    public function getOverdraftLimit(): ?float;

    /**
     * @param float $overdraftLimit
     * @return void
     */
    public function setOverdraftLimit(float $overdraftLimit): void;

    /**
     * @return int
     */
    public function getOverdraftRepay(): int;

    /**
     * @param int $overdraftRepay
     * @return void
     */
    public function setOverdraftRepay(int $overdraftRepay): void;

    /**
     * @return int|null
     */
    public function getOverdraftRepayDigit(): ?int;

    /**
     * @param float $overdraftRepayDigit
     * @return void
     */
    public function setOverdraftRepayDigit(float $overdraftRepayDigit): void;

    /**
     * @return int|null
     */
    public function getOverdraftRepayType(): ?int;

    /**
     * @param int $overdraftRepayType
     * @return void
     */
    public function setOverdraftRepayType(int $overdraftRepayType): void;

    /**
     * @return float|null
     */
    public function getOverdraftPenalty(): ?float;

    /**
     * @param float $overdraftPenalty
     * @return void
     */
    public function setOverdraftPenalty(float $overdraftPenalty): void;
}
