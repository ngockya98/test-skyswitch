<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Api\Data;

interface OverdraftInterface
{
    public const MAIN_TABLE = 'amasty_company_credit_overdraft';

    public const ID = 'id';
    public const CREDIT_ID = 'credit_id';
    public const START_DATE = 'start_date';
    public const REPAY_DATE = 'repay_date';

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
    public function getCreditId(): ?int;

    /**
     * @param int $creditId
     * @return void
     */
    public function setCreditId(int $creditId): void;

    /**
     * @return string|null
     */
    public function getStartDate(): ?string;

    /**
     * @param string $startDate
     * @return void
     */
    public function setStartDate(string $startDate): void;

    /**
     * @return string|null
     */
    public function getRepayDate(): ?string;

    /**
     * @param string $repayDate
     * @return void
     */
    public function setRepayDate(string $repayDate): void;
}
