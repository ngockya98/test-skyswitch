<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Company\Form;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Model\Backend\Company\Registry as CompanyRegistry;
use Amasty\CompanyAccount\Model\Price\Format as FormatPrice;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

/**
 * Need be used for credit cards.
 * Expected that current editable company in CompanyRegistry.
 * @see \Amasty\CompanyAccount\Model\Backend\Company\Registry
 */
abstract class PriceField extends Field
{
    /**
     * @var CompanyRegistry
     */
    private $companyRegistry;

    /**
     * @var FormatPrice
     */
    private $formatPrice;

    public function __construct(
        CompanyRegistry $companyRegistry,
        FormatPrice $formatPrice,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->companyRegistry = $companyRegistry;
        $this->formatPrice = $formatPrice;
    }

    protected function formatPrice(float $price): string
    {
        return $this->formatPrice->execute($price, $this->getCredit()->getCurrencyCode());
    }

    private function getCredit(): CreditInterface
    {
        return $this->companyRegistry->get()->getExtensionAttributes()->getCredit();
    }
}
