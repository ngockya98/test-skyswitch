<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Company\Form;

use Amasty\CompanyAccount\Api\Data\CreditInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Model\Backend\Company\Registry as CompanyRegistry;
use Amasty\CompanyAccount\Model\Price\Format as FormatPrice;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class BePaid extends PriceField
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    public function __construct(
        CompanyRegistry $companyRegistry,
        FormatPrice $formatPrice,
        TimezoneInterface $timezone,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($companyRegistry, $formatPrice, $context, $uiComponentFactory, $components, $data);
        $this->timezone = $timezone;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['store_credit'])) {
            $storeCreditData = &$dataSource['data']['store_credit'];
            $price = isset($storeCreditData[CreditInterface::BE_PAID])
                ? (float) $storeCreditData[CreditInterface::BE_PAID]
                : 0;
            $storeCreditData[CreditInterface::BE_PAID] = $this->formatPrice($price);
            if (isset($storeCreditData['overdraft'][OverdraftInterface::REPAY_DATE])) {
                $storeCreditData['overdraft'][OverdraftInterface::REPAY_DATE] = $this->timezone->formatDateTime(
                    $storeCreditData['overdraft'][OverdraftInterface::REPAY_DATE],
                    \IntlDateFormatter::MEDIUM
                );
            }
        }

        return $dataSource;
    }
}
