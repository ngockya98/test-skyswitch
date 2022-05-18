<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var OverdraftInterfaceFactory
     */
    private $overdraftInterfaceFactory;

    public function __construct(OverdraftInterfaceFactory $overdraftInterfaceFactory)
    {
        $this->overdraftInterfaceFactory = $overdraftInterfaceFactory;
    }

    public function execute(): OverdraftInterface
    {
        return $this->overdraftInterfaceFactory->create();
    }
}
