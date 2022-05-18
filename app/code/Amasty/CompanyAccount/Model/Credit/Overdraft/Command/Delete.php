<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Command;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Model\Overdraft;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft as OverdraftResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

class Delete implements DeleteInterface
{
    /**
     * @var OverdraftResource
     */
    private $overdraftResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        OverdraftResource $overdraftResource,
        LoggerInterface $logger
    ) {
        $this->overdraftResource = $overdraftResource;
        $this->logger = $logger;
    }

    /**
     * @param OverdraftInterface|Overdraft $overdraft
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(OverdraftInterface $overdraft): void
    {
        try {
            $this->overdraftResource->delete($overdraft);
        } catch (\Exception $e) {
            if ($overdraft->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove overdraft with ID %1. Error: %2',
                        [$overdraft->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove overdraft. Error: %1', $e->getMessage()));
        }
    }
}
