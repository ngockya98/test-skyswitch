<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Company\Form;

use Amasty\CompanyAccount\Api\Data\CreditInterface;

class Balance extends PriceField
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['store_credit'])) {
            $dataSource['data']['store_credit'] = [];
        }

        $storeCreditData = &$dataSource['data']['store_credit'];
        $fieldsToFormat = [
            CreditInterface::BALANCE,
            CreditInterface::ISSUED_CREDIT,
            CreditInterface::OVERDRAFT_LIMIT
        ];
        foreach ($fieldsToFormat as $fieldToFormat) {
            $price = isset($storeCreditData[$fieldToFormat])
                ? (float) $storeCreditData[$fieldToFormat]
                : 0;
            $storeCreditData[sprintf('%s_for_card', $fieldToFormat)] = $this->formatPrice($price);
        }

        return $dataSource;
    }
}
