<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Model\ResourceModel\Overdraft as OverdraftResource;
use Magento\Framework\Model\AbstractModel;

class Overdraft extends AbstractModel implements OverdraftInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(OverdraftResource::class);
    }

    public function getCreditId(): ?int
    {
        return $this->hasData(OverdraftInterface::CREDIT_ID)
            ? (int) $this->_getData(OverdraftInterface::CREDIT_ID)
            : null;
    }

    public function setCreditId(int $creditId): void
    {
        $this->setData(OverdraftInterface::CREDIT_ID, $creditId);
    }

    public function getStartDate(): ?string
    {
        return $this->_getData(OverdraftInterface::START_DATE);
    }

    public function setStartDate(string $startDate): void
    {
        $this->setData(OverdraftInterface::START_DATE, $startDate);
    }

    public function getRepayDate(): ?string
    {
        return $this->_getData(OverdraftInterface::REPAY_DATE);
    }

    public function setRepayDate(string $repayDate): void
    {
        $this->setData(OverdraftInterface::REPAY_DATE, $repayDate);
    }
}
