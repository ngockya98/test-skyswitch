<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use DateTimeZoneFactory;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * @api
 */
class CalculateRepayDate
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var DateTimeZoneFactory
     */
    private $dateTimeZoneFactory;

    /**
     * @var ModifyDateToRepayDate
     */
    private $modifyDateToRepayDate;

    public function __construct(
        DateTimeFactory $dateTimeFactory,
        DateTimeZoneFactory $dateTimeZoneFactory,
        ModifyDateToRepayDate $modifyDateToRepayDate
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->dateTimeZoneFactory = $dateTimeZoneFactory;
        $this->modifyDateToRepayDate = $modifyDateToRepayDate;
    }

    public function execute(CreditInterface $credit): string
    {
        $dateTime = $this->dateTimeFactory->create('now', $this->dateTimeZoneFactory->create([
            'timezone' => 'UTC'
        ]));

        $this->modifyDateToRepayDate->execute(
            $dateTime,
            $credit->getOverdraftRepayDigit(),
            $credit->getOverdraftRepayType()
        );

        return $dateTime->format(CreateOverdraft::DATETIME_FORMAT);
    }
}
