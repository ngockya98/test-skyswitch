<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\Component\Listing\Column;

use Amasty\CompanyAccount\Model\CustomerDataProvider;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class CompanyName extends Column
{
    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    public function __construct(
        CustomerDataProvider $customerDataProvider,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->customerDataProvider = $customerDataProvider;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['customer_id']) && !isset($item['company_name'])) {
                    $item['company_name'] =
                        $this->customerDataProvider->getCompanyNameByCustomerId((int)$item['customer_id']);
                }
            }
        }

        return $dataSource;
    }
}
