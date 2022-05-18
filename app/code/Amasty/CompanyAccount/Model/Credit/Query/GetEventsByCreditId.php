<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\Collection as CreditEventCollection;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\CollectionFactory;

class GetEventsByCreditId implements GetEventsByCreditIdInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(int $creditId): CreditEventCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CreditEventInterface::CREDIT_ID, $creditId);
        return $collection;
    }
}
