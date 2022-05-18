<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Ui\DataProvider\CreditEvent;

use Amasty\CompanyAccount\Api\Data\CreditEventInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $creditId = (int) $this->request->getParam(CreditEventInterface::CREDIT_ID);
            $this->filterBuilder->setField(CreditEventInterface::CREDIT_ID);
            $this->filterBuilder->setValue($creditId);
            $this->addFilter($this->filterBuilder->create());
        }

        return parent::getSearchCriteria();
    }
}
