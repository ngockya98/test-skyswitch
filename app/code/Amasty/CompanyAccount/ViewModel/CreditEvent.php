<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\ViewModel;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;
use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\Credit\Event\RetrieveComments;
use Amasty\CompanyAccount\Model\Credit\Query\GetEventsByCreditIdInterface;
use Amasty\CompanyAccount\Model\Price\Convert as ConvertPrice;
use Amasty\CompanyAccount\Model\Price\Format as FormatPrice;
use Amasty\CompanyAccount\Model\ResourceModel\CreditEvent\Collection as CreditEventCollection;
use Amasty\CompanyAccount\Model\Source\Credit\Operation as OperationTypes;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CreditEvent implements ArgumentInterface
{
    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var GetEventsByCreditIdInterface
     */
    private $getEventsByCreditId;

    /**
     * @var array
     */
    private $operationTypes;

    /**
     * @var ConvertPrice
     */
    private $convertPrice;

    /**
     * @var FormatPrice
     */
    private $formatPrice;

    /**
     * @var RetrieveComments
     */
    private $retrieveComments;

    public function __construct(
        CompanyContext $companyContext,
        GetEventsByCreditIdInterface $getEventsByCreditId,
        OperationTypes $operationTypesSource,
        ConvertPrice $convertPrice,
        FormatPrice $formatPrice,
        RetrieveComments $retrieveComments
    ) {
        $this->companyContext = $companyContext;
        $this->getEventsByCreditId = $getEventsByCreditId;
        $this->operationTypes = $operationTypesSource->toArray();
        $this->convertPrice = $convertPrice;
        $this->formatPrice = $formatPrice;
        $this->retrieveComments = $retrieveComments;
    }

    public function getCreditEventsForCompanyContext(): CreditEventCollection
    {
        $currentCompany = $this->companyContext->getCurrentCompany();
        $collection = $this->getEventsByCreditId->execute(
            (int) $currentCompany->getExtensionAttributes()->getCredit()->getId()
        );
        $collection->addOrder(CreditEventInterface::DATE, CreditEventCollection::SORT_ORDER_DESC);

        return $collection;
    }

    public function getTypeLabel(string $type): Phrase
    {
        return $this->operationTypes[$type] ?? __('Undefined.');
    }

    public function getAmount(CreditEventInterface $creditEvent): string
    {
        $amount = $this->convert((float) $creditEvent->getAmount(), (float) $creditEvent->getRate());
        $formattedAmount = $this->formatPrice->execute($amount, $creditEvent->getCurrencyEvent());
        return $creditEvent->getAmount() > 0 ? sprintf('+%s', $formattedAmount) : $formattedAmount;
    }

    public function getBalance(CreditEventInterface $creditEvent): string
    {
        return $this->formatPrice->execute($creditEvent->getBalance(), $creditEvent->getCurrencyCredit());
    }

    public function getComment(CreditEventInterface $creditEvent): string
    {
        return $this->retrieveComments->execute($creditEvent);
    }

    private function convert(float $price, float $rate)
    {
        if ($rate) {
            $price *= $rate;
        }

        return $price;
    }
}
