<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Command\SaveInterface as CommandSave;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\GetByCreditIdInterface as GetOverdraftByCreditId;
use DateTimeZoneFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;

class UpdateRepayDate
{
    /**
     * @var GetOverdraftByCreditId
     */
    private $getOverdraftByCreditId;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var DateTimeZoneFactory
     */
    private $dateTimeZoneFactory;

    /**
     * @var CommandSave
     */
    private $saveCommand;

    /**
     * @var ModifyDateToRepayDate
     */
    private $modifyDateToRepayDate;

    public function __construct(
        GetOverdraftByCreditId $getOverdraftByCreditId,
        DateTimeFactory $dateTimeFactory,
        DateTimeZoneFactory $dateTimeZoneFactory,
        CommandSave $saveCommand,
        ModifyDateToRepayDate $modifyDateToRepayDate
    ) {
        $this->getOverdraftByCreditId = $getOverdraftByCreditId;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->dateTimeZoneFactory = $dateTimeZoneFactory;
        $this->saveCommand = $saveCommand;
        $this->modifyDateToRepayDate = $modifyDateToRepayDate;
    }

    /**
     * @param CreditInterface $credit
     * @return void
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function execute(CreditInterface $credit): void
    {
        $overdraft = $this->getOverdraftByCreditId->execute((int) $credit->getId());

        $currentDate = $this->dateTimeFactory->create('now', $this->dateTimeZoneFactory->create([
            'timezone' => 'UTC'
        ]));

        $endDate = $this->dateTimeFactory->create($overdraft->getStartDate(), $this->dateTimeZoneFactory->create([
            'timezone' => 'UTC'
        ]));
        $this->modifyDateToRepayDate->execute(
            $endDate,
            $credit->getOverdraftRepayDigit(),
            $credit->getOverdraftRepayType()
        );

        if ($endDate <= $currentDate) {
            throw new LocalizedException(__('Can\'t update overdraft for this time.'));
        }

        $overdraft->setRepayDate($endDate->format(CreateOverdraft::DATETIME_FORMAT));
        $this->saveCommand->execute($overdraft);
    }
}
