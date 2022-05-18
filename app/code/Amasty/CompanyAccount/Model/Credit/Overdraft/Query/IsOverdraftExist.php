<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Overdraft\Query;

use Amasty\CompanyAccount\Model\Credit\Overdraft\Query\GetByCreditIdInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class IsOverdraftExist implements IsOverdraftExistInterface
{
    /**
     * @var GetByCreditIdInterface
     */
    private $getByCreditId;

    public function __construct(GetByCreditIdInterface $getByCreditId)
    {
        $this->getByCreditId = $getByCreditId;
    }

    public function execute(int $creditId): bool
    {
        try {
            $this->getByCreditId->execute($creditId);
            $result = true;
        } catch (NoSuchEntityException $e) {
            $result = false;
        }

        return $result;
    }
}
