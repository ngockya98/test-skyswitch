<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\CreditEvent;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Magento\Framework\App\ResourceConnection;

class SaveMultiple
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param CreditEventInterface[] $creditEvents
     * @return void
     */
    public function execute(array $creditEvents): void
    {
        $connection = $this->resourceConnection->getConnection('sales');

        $columns = [
            CreditEventInterface::CREDIT_ID,
            CreditEventInterface::TYPE,
            CreditEventInterface::AMOUNT,
            CreditEventInterface::RATE,
            CreditEventInterface::RATE_CREDIT,
            CreditEventInterface::CURRENCY_EVENT,
            CreditEventInterface::CURRENCY_CREDIT,
            CreditEventInterface::BALANCE,
            CreditEventInterface::COMMENT
        ];

        $data = [];
        foreach ($creditEvents as $creditEvent) {
            $data[] = [
                $creditEvent->getCreditId(),
                $creditEvent->getType(),
                $creditEvent->getAmount(),
                $creditEvent->getRate(),
                $creditEvent->getCreditRate(),
                $creditEvent->getCurrencyEvent(),
                $creditEvent->getCurrencyCredit(),
                $creditEvent->getBalance(),
                $creditEvent->getComment()
            ];
        }

        $connection->insertArray(
            $this->resourceConnection->getTableName(CreditEventInterface::MAIN_TABLE),
            $columns,
            $data
        );
    }
}
