<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterfaceFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Credit as CreditResource;
use Magento\Framework\Exception\NoSuchEntityException;

class GetById implements GetByIdInterface
{
    /**
     * @var CreditInterfaceFactory
     */
    private $creditFactory;

    /**
     * @var CreditResource
     */
    private $creditResource;

    public function __construct(
        CreditInterfaceFactory $creditFactory,
        CreditResource $creditResource
    ) {
        $this->creditFactory = $creditFactory;
        $this->creditResource = $creditResource;
    }

    /**
     * @param int $id
     * @return CreditInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): CreditInterface
    {
        /** @var CreditInterface $credit */
        $credit = $this->creditFactory->create();
        $this->creditResource->load($credit, $id);

        if ($credit->getId() === null) {
            throw new NoSuchEntityException(
                __('Credit with id "%value" does not exist.', ['value' => $id])
            );
        }

        return $credit;
    }
}
