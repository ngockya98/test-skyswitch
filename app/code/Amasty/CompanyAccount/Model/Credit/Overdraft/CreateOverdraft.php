<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Command\SaveInterface as CommandSave;
use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\GetNewInterface as GetNewOverdraft;
use DateTimeZoneFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * @api
 */
class CreateOverdraft
{
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var GetNewOverdraft
     */
    private $getNewOverdraft;

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
        GetNewOverdraft $getNewOverdraft,
        DateTimeFactory $dateTimeFactory,
        DateTimeZoneFactory $dateTimeZoneFactory,
        CommandSave $saveCommand,
        ModifyDateToRepayDate $modifyDateToRepayDate
    ) {
        $this->getNewOverdraft = $getNewOverdraft;
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
        $dateTime = $this->dateTimeFactory->create('now', $this->dateTimeZoneFactory->create([
            'timezone' => 'UTC'
        ]));
        $startDate = $dateTime->format(self::DATETIME_FORMAT);
        $this->modifyDateToRepayDate->execute(
            $dateTime,
            $credit->getOverdraftRepayDigit(),
            $credit->getOverdraftRepayType()
        );
        $repayDate = $dateTime->format(self::DATETIME_FORMAT);

        $overdraft = $this->getNewOverdraft->execute();

        $overdraft->setCreditId((int) $credit->getId());
        $overdraft->setStartDate($startDate);
        $overdraft->setRepayDate($repayDate);

        $this->saveCommand->execute($overdraft);
    }
}
