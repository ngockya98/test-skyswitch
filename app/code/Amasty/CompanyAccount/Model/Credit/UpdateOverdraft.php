<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\OverdraftRepositoryInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\CreateOverdraft;
use Amasty\CompanyAccount\Model\Credit\Overdraft\UpdateRepayDate;
use Amasty\CompanyAccount\Model\Source\Credit\OverdraftRepay;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateOverdraft
{
    /**
     * @var OverdraftRepositoryInterface
     */
    private $overdraftRepository;

    /**
     * @var CreateOverdraft
     */
    private $createOverdraft;

    /**
     * @var UpdateRepayDate
     */
    private $updateOverdraftRepayDate;

    public function __construct(
        OverdraftRepositoryInterface $overdraftRepository,
        CreateOverdraft $createOverdraft,
        UpdateRepayDate $updateOverdraftRepayDate
    ) {
        $this->overdraftRepository = $overdraftRepository;
        $this->createOverdraft = $createOverdraft;
        $this->updateOverdraftRepayDate = $updateOverdraftRepayDate;
    }

    /**
     * @param CreditInterface $credit
     * @return void
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(CreditInterface $credit): void
    {
        if ($credit->isOverdraftAllowed()
            && $credit->getOverdraftRepay() === OverdraftRepay::SET
            && $credit->getBalance() < 0
        ) {
            $timePeriodChanged = $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_REPAY_TYPE)
                || $credit->dataHasChangedFor(CreditInterface::OVERDRAFT_REPAY_DIGIT);
            $isOverdraftExist = $this->overdraftRepository->isExistForCredit((int) $credit->getId());

            if ($timePeriodChanged && $isOverdraftExist) {
                $this->updateOverdraftRepayDate->execute($credit);
            } elseif (!$isOverdraftExist) {
                $this->createOverdraft->execute($credit);
            }
        } elseif ($this->overdraftRepository->isExistForCredit((int) $credit->getId())) {
            $overdraft = $this->overdraftRepository->getByCreditId((int) $credit->getId());
            $this->overdraftRepository->delete($overdraft);
        }
    }
}
