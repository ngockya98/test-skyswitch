<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Credit;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Api\OverdraftRepositoryInterface;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\Credit;
use Amasty\CompanyAccount\Model\Price\Format as FormatPrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Card extends Template
{
    /**
     * @var FormatPrice
     */
    private $formatPrice;

    /**
     * @var OverdraftRepositoryInterface
     */
    private $overdraftRepository;

    public function __construct(
        FormatPrice $formatPrice,
        OverdraftRepositoryInterface $overdraftRepository,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formatPrice = $formatPrice;
        $this->overdraftRepository = $overdraftRepository;
    }

    /**
     * @return CreditInterface|Credit
     */
    public function getCredit(): CreditInterface
    {
        /** @var CompanyContext $companyContext */
        $companyContext = $this->getData('companyContext');
        return $companyContext->getCurrentCompany()->getExtensionAttributes()->getCredit();
    }

    /**
     * Returned formatted price field for current credit currency.
     *
     * @param string $fieldName Name of field which must be formatted in credit currency.
     * @return string
     */
    public function getCreditPrice(string $fieldName): string
    {
        $credit = $this->getCredit();
        return $this->formatPrice->execute((float) $credit->getData($fieldName), $credit->getCurrencyCode());
    }

    public function getOverdraftSum(): string
    {
        return ltrim($this->getCreditPrice(CreditInterface::BALANCE), '-');
    }

    public function isOverdraftExist(): bool
    {
        return $this->overdraftRepository->isExistForCredit((int) $this->getCredit()->getId());
    }

    public function isOverdraftExceed(): bool
    {
        return $this->overdraftRepository->isOverdraftExceed((int) $this->getCredit()->getId());
    }

    /**
     * @return OverdraftInterface
     * @throws NoSuchEntityException
     */
    public function getOverdraft(): OverdraftInterface
    {
        return $this->overdraftRepository->getByCreditId((int) $this->getCredit()->getId());
    }
}
