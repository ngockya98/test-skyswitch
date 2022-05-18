<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Extensions\Credit;

use Amasty\CompanyAccount\Api\CreditRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Credit;
use Amasty\CompanyAccount\Model\Price\Convert as ConvertPrice;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var CreditRepositoryInterface
     */
    private $creditRepository;

    /**
     * @var ConvertPrice
     */
    private $convertPrice;

    public function __construct(
        CreditRepositoryInterface $creditRepository,
        ConvertPrice $convertPrice
    ) {
        $this->creditRepository = $creditRepository;
        $this->convertPrice = $convertPrice;
    }

    /**
     * @param CompanyInterface $entity
     * @param array $arguments
     * @return CompanyInterface
     */
    public function execute($entity, $arguments = [])
    {
        /** @var CreditInterface|Credit $credit */
        $credit = $entity->getExtensionAttributes()->getCredit();

        if ($credit) {
            if ($credit->dataHasChangedFor(CreditInterface::CURRENCY_CODE)) {
                $credit->setBalance($this->getConvertedValue($credit, CreditInterface::BALANCE));
                $credit->setBePaid($this->getConvertedValue($credit, CreditInterface::BE_PAID));
                $credit->setIssuedCredit($this->getConvertedValue($credit, CreditInterface::ISSUED_CREDIT));
            }
            $credit->setCompanyId($entity->getCompanyId());
            $this->creditRepository->save($credit);
        }

        return $entity;
    }

    /**
     * @param CreditInterface|Credit $credit
     * @param string $field
     * @return float
     */
    private function getConvertedValue(CreditInterface $credit, string $field): float
    {
        $fromCurrency = $credit->getOrigData(CreditInterface::CURRENCY_CODE);
        $toCurrency = $credit->getData(CreditInterface::CURRENCY_CODE);

        return $this->convertPrice->execute((float) $credit->getData($field), $fromCurrency, $toCurrency);
    }
}
