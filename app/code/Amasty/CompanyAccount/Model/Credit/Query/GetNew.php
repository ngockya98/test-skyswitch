<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var CreditInterfaceFactory
     */
    private $creditFactory;

    public function __construct(CreditInterfaceFactory $creditFactory)
    {
        $this->creditFactory = $creditFactory;
    }

    public function execute(): CreditInterface
    {
        return $this->creditFactory->create();
    }
}
