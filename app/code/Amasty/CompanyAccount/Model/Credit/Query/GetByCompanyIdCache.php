<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Credit\Query;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByCompanyIdCache implements GetByCompanyIdInterface
{
    /**
     * @var array
     */
    private $credits = [];

    /**
     * @var GetByCompanyId
     */
    private $getByCompanyId;

    public function __construct(GetByCompanyId $getByCompanyId)
    {
        $this->getByCompanyId = $getByCompanyId;
    }

    /**
     * @param int $companyId
     * @return CreditInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $companyId): CreditInterface
    {
        if (!isset($this->credits[$companyId])) {
            $this->credits[$companyId] = $this->getByCompanyId->execute($companyId);
        }

        return $this->credits[$companyId];
    }
}
