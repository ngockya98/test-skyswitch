<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Amasty\CompanyAccount\Model\ResourceModel\Order as CompanyOrderResource;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Sales\Api\Data\OrderInterface;

class CompanyFilter implements CustomFilterInterface
{
    /**
     * @var CompanyOrderResource
     */
    private $companyOrderResource;

    public function __construct(CompanyOrderResource $companyOrderResource)
    {
        $this->companyOrderResource = $companyOrderResource;
    }

    /**
     * Needed because in select count sql magento reset left joins => lost info about company_id & cant filter
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        $collection->getSelect()->where(sprintf(
            'main_table.%s in (%s)',
            OrderInterface::ENTITY_ID,
            $this->companyOrderResource->getCompanyOrders((int) $filter->getValue())
        ));

        return true;
    }
}
