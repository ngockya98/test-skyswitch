<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Overdraft;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Magento\Framework\App\ResourceConnection;

class GetCreditIdsForPenalty
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName(OverdraftInterface::MAIN_TABLE),
            [OverdraftInterface::CREDIT_ID]
        )->where(sprintf(
            '%s < UTC_TIMESTAMP()',
            OverdraftInterface::REPAY_DATE
        ));

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
