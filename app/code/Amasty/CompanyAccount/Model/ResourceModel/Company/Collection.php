<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel\Company;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\CompanyAccount\Model\Company::class,
            \Amasty\CompanyAccount\Model\ResourceModel\Company::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param array $companyIds
     * @return $this
     */
    public function addCompanyIdFilter($companyIds)
    {
        if (!is_array($companyIds)) {
            $companyIds = [$companyIds];
        }

        $this->addFieldToFilter(CompanyInterface::COMPANY_ID, ['in' => $companyIds]);

        return $this;
    }
}
