<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Overdraft;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\Source\Credit\Operation;
use Magento\Framework\App\ResourceConnection;

class IsPenaltyAppliedForCredit
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $creditId): bool
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(CreditEventInterface::MAIN_TABLE),
            [CreditEventInterface::ID]
        )->where(
            sprintf('%s = ?', CreditEventInterface::CREDIT_ID),
            $creditId
        )->where(
            sprintf('%s = ?', CreditEventInterface::TYPE),
            Operation::OVERDRAFT_PENALTY
        )->where(
            sprintf('%s + interval 1 day > UTC_TIMESTAMP()', CreditEventInterface::DATE)
        );

        return (bool) $this->resourceConnection->getConnection()->fetchOne($select);
    }
}
