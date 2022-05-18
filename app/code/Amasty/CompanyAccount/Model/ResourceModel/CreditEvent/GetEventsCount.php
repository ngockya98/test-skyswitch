<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\CreditEvent;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\CollectionFactory as CreditEventCollectionFactory;

class GetEventsCount
{
    /**
     * @var CollectionFactory
     */
    private $creditEventCollectionFactory;

    public function __construct(CreditEventCollectionFactory $creditEventCollectionFactory)
    {
        $this->creditEventCollectionFactory = $creditEventCollectionFactory;
    }

    public function execute(int $creditId, ?string $eventType = null): int
    {
        $creditEventCollection = $this->creditEventCollectionFactory->create();
        $creditEventCollection->addFieldToFilter(CreditEventInterface::CREDIT_ID, $creditId);
        if ($eventType !== null) {
            $creditEventCollection->addFieldToFilter(CreditEventInterface::TYPE, $eventType);
        }

        return $creditEventCollection->getSize();
    }
}
