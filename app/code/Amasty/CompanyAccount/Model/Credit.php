<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit\UpdateOverdraft;
use Amasty\CompanyAccount\Model\ResourceModel\Credit as CreditResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Credit extends AbstractModel implements CreditInterface
{
    /**
     * @var UpdateOverdraft
     */
    private $updateOverdraft;

    public function __construct(
        Context $context,
        Registry $registry,
        UpdateOverdraft $updateOverdraft,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->updateOverdraft = $updateOverdraft;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(CreditResource::class);
    }

    /**
     * @return Credit
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterSave()
    {
        $this->updateOverdraft->execute($this);
        return parent::afterSave();
    }

    public function getCompanyId(): ?int
    {
        return $this->hasData(CreditInterface::COMPANY_ID)
            ? (int) $this->_getData(CreditInterface::COMPANY_ID)
            : null;
    }

    public function setCompanyId(int $companyId): void
    {
        $this->setData(CreditInterface::COMPANY_ID, $companyId);
    }

    public function getBalance(): float
    {
        return (float) $this->_getData(CreditInterface::BALANCE);
    }

    public function setBalance(float $balance): void
    {
        $this->setData(CreditInterface::BALANCE, $balance);
    }

    public function getIssuedCredit(): float
    {
        return (float) $this->_getData(CreditInterface::ISSUED_CREDIT);
    }

    public function setIssuedCredit(float $issuedCredit): void
    {
        $this->setData(CreditInterface::ISSUED_CREDIT, $issuedCredit);
    }

    public function getBePaid(): float
    {
        return (float) $this->_getData(CreditInterface::BE_PAID);
    }

    public function setBePaid(float $bePaid): void
    {
        $this->setData(CreditInterface::BE_PAID, $bePaid);
    }

    public function getCurrencyCode(): ?string
    {
        return $this->_getData(CreditInterface::CURRENCY_CODE);
    }

    public function setCurrencyCode(string $currencyCode): void
    {
        $this->setData(CreditInterface::CURRENCY_CODE, $currencyCode);
    }

    public function isOverdraftAllowed(): bool
    {
        return (bool) $this->_getData(CreditInterface::ALLOW_OVERDRAFT);
    }

    public function setOverdraftAllowed(bool $overdraftAllowed): void
    {
        $this->setData(CreditInterface::ALLOW_OVERDRAFT, $overdraftAllowed);
    }

    public function getOverdraftLimit(): ?float
    {
        return $this->hasData(CreditInterface::OVERDRAFT_LIMIT)
            ? (float) $this->_getData(CreditInterface::OVERDRAFT_LIMIT)
            : null;
    }

    public function setOverdraftLimit(float $overdraftLimit): void
    {
        $this->setData(CreditInterface::OVERDRAFT_LIMIT, $overdraftLimit);
    }

    public function getOverdraftRepay(): int
    {
        return (int) $this->getData(CreditInterface::OVERDRAFT_REPAY_PERIOD);
    }

    public function setOverdraftRepay(int $overdraftRepay): void
    {
        $this->setData(CreditInterface::OVERDRAFT_REPAY_PERIOD, $overdraftRepay);
    }

    public function getOverdraftRepayDigit(): ?int
    {
        return $this->hasData(CreditInterface::OVERDRAFT_REPAY_DIGIT)
            ? (int) $this->_getData(CreditInterface::OVERDRAFT_REPAY_DIGIT)
            : null;
    }

    public function setOverdraftRepayDigit(float $overdraftRepayDigit): void
    {
        $this->setData(CreditInterface::OVERDRAFT_REPAY_DIGIT, $overdraftRepayDigit);
    }

    public function getOverdraftRepayType(): ?int
    {
        return $this->hasData(CreditInterface::OVERDRAFT_REPAY_TYPE)
            ? (int) $this->_getData(CreditInterface::OVERDRAFT_REPAY_TYPE)
            : null;
    }

    public function setOverdraftRepayType(int $overdraftRepayType): void
    {
        $this->setData(CreditInterface::OVERDRAFT_REPAY_TYPE, $overdraftRepayType);
    }

    public function getOverdraftPenalty(): ?float
    {
        return $this->hasData(CreditInterface::OVERDRAFT_PENALTY)
            ? (float) $this->_getData(CreditInterface::OVERDRAFT_PENALTY)
            : null;
    }

    public function setOverdraftPenalty(float $overdraftPenalty): void
    {
        $this->setData(CreditInterface::OVERDRAFT_PENALTY, $overdraftPenalty);
    }
}
