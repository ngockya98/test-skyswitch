<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Repository;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Api\OverdraftRepositoryInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Command\DeleteInterface as CommandDelete;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Command\SaveInterface as CommandSave;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\GetByCreditIdInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\GetNewInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\IsExceedInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\IsOverdraftExistInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OverdraftRepository implements OverdraftRepositoryInterface
{
    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var GetByCreditIdInterface
     */
    private $getByCreditId;

    /**
     * @var CommandSave
     */
    private $commandSave;

    /**
     * @var IsOverdraftExistInterface
     */
    private $isOverdraftExist;

    /**
     * @var CommandDelete
     */
    private $commandDelete;

    /**
     * @var IsExceedInterface
     */
    private $isExceed;

    public function __construct(
        GetNewInterface $getNew,
        GetByCreditIdInterface $getByCreditId,
        CommandSave $commandSave,
        IsOverdraftExistInterface $isOverdraftExist,
        CommandDelete $commandDelete,
        IsExceedInterface $isExceed
    ) {
        $this->getNew = $getNew;
        $this->getByCreditId = $getByCreditId;
        $this->commandSave = $commandSave;
        $this->isOverdraftExist = $isOverdraftExist;
        $this->commandDelete = $commandDelete;
        $this->isExceed = $isExceed;
    }

    public function getNew(): OverdraftInterface
    {
        return $this->getNew->execute();
    }

    /**
     * @param int $creditId
     * @return OverdraftInterface
     * @throws NoSuchEntityException
     */
    public function getByCreditId(int $creditId): OverdraftInterface
    {
        return $this->getByCreditId->execute($creditId);
    }

    /**
     * @param OverdraftInterface $overdraft
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(OverdraftInterface $overdraft): void
    {
        $this->commandSave->execute($overdraft);
    }

    public function isExistForCredit(int $creditId): bool
    {
        return $this->isOverdraftExist->execute($creditId);
    }

    /**
     * @param OverdraftInterface $overdraft
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(OverdraftInterface $overdraft): void
    {
        $this->commandDelete->execute($overdraft);
    }

    public function isOverdraftExceed(int $creditId): bool
    {
        return $this->isExceed->execute($creditId);
    }
}
