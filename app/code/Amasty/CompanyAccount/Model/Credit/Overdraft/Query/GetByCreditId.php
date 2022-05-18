<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterfaceFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft as OverdraftResource;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByCreditId implements GetByCreditIdInterface
{
    /**
     * @var OverdraftInterfaceFactory
     */
    private $overdraftFactory;

    /**
     * @var OverdraftResource
     */
    private $overdraftResource;

    public function __construct(
        OverdraftInterfaceFactory $overdraftFactory,
        OverdraftResource $overdraftResource
    ) {
        $this->overdraftFactory = $overdraftFactory;
        $this->overdraftResource = $overdraftResource;
    }

    /**
     * @param int $creditId
     * @return OverdraftInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $creditId): OverdraftInterface
    {
        /** @var OverdraftInterface $overdraft */
        $overdraft = $this->overdraftFactory->create();
        $this->overdraftResource->load($overdraft, $creditId, OverdraftInterface::CREDIT_ID);

        if ($overdraft->getId() === null) {
            throw new NoSuchEntityException(
                __('Overdraft with credit id "%value" does not exist.', ['value' => $creditId])
            );
        }

        return $overdraft;
    }
}
