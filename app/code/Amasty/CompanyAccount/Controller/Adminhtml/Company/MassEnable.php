<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Magento\Framework\Data\Collection\AbstractDb;
use Amasty\CompanyAccount\Model\Source\Company\Status;

class MassEnable extends \Amasty\CompanyAccount\Controller\Adminhtml\Company\MassActionAbstract
{
    /**
     * @param AbstractDb $collection
     */
    public function doAction(AbstractDb $collection)
    {
        $collectionSize = $collection->getSize();
        foreach ($collection as $company) {
            $company->setStatus(Status::STATUS_ACTIVE);
            $this->companyRepository->save($company);
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been activated.', $collectionSize));
    }
}
