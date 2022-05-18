<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\CreditEvent;

use Amasty\CompanyAccount\Api\CreditRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Exception;
use Magento\Framework\App\ResourceConnection;

class Append
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CreditRepositoryInterface
     */
    private $creditRepository;

    /**
     * @var SaveMultiple
     */
    private $saveMultiple;

    public function __construct(
        ResourceConnection $resourceConnection,
        CreditRepositoryInterface $creditRepository,
        SaveMultiple $saveMultiple
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->creditRepository = $creditRepository;
        $this->saveMultiple = $saveMultiple;
    }

    /**
     * @param CreditInterface $credit
     * @param CreditEventInterface[] $creditEvents
     * @return void
     * @throws Exception
     */
    public function execute(CreditInterface $credit, array $creditEvents): void
    {
        $connection = $this->resourceConnection->getConnection('sales');

        $connection->beginTransaction();
        try {
            $this->creditRepository->save($credit);
            $this->saveMultiple->execute($creditEvents);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
