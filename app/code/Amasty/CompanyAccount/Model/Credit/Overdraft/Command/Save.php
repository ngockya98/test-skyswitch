<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Command;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Model\Overdraft;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft as OverdraftResource;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
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
     * @throws CouldNotSaveException
     */
    public function execute(OverdraftInterface $overdraft): void
    {
        try {
            $this->overdraftResource->save($overdraft);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Overdraft'), $e);
        }
    }
}
