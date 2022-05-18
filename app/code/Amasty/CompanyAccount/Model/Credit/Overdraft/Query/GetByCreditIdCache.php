<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByCreditIdCache implements GetByCreditIdInterface
{
    /**
     * @var array
     */
    private $overdrafts = [];

    /**
     * @var GetByCreditId
     */
    private $getByCreditId;

    public function __construct(GetByCreditId $getByCreditId)
    {
        $this->getByCreditId = $getByCreditId;
    }

    /**
     * @param int $creditId
     * @return OverdraftInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $creditId): OverdraftInterface
    {
        if (!isset($this->overdrafts[$creditId])) {
            $this->overdrafts[$creditId] = $this->getByCreditId->execute($creditId);
        }

        return $this->overdrafts[$creditId];
    }
}
