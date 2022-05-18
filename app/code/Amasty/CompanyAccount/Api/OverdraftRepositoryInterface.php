<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Api;

/**
 * @api
 */
interface OverdraftRepositoryInterface
{
    /**
     * @return \Amasty\CompanyAccount\Api\Data\OverdraftInterface
     */
    public function getNew(): \Amasty\CompanyAccount\Api\Data\OverdraftInterface;

    /**
     * @param int $creditId
     * @return \Amasty\CompanyAccount\Api\Data\OverdraftInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCreditId(int $creditId): \Amasty\CompanyAccount\Api\Data\OverdraftInterface;

    /**
     * @param \Amasty\CompanyAccount\Api\Data\OverdraftInterface $overdraft
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\CompanyAccount\Api\Data\OverdraftInterface $overdraft): void;

    /**
     * @param \Amasty\CompanyAccount\Api\Data\OverdraftInterface $overdraft
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\CompanyAccount\Api\Data\OverdraftInterface $overdraft): void;

    /**
     * @param int $creditId
     * @return bool
     */
    public function isExistForCredit(int $creditId): bool;

    /**
     * @param int $creditId
     * @return bool
     */
    public function isOverdraftExceed(int $creditId): bool;
}
