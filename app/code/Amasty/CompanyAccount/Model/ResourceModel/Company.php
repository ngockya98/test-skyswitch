<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;

class Company extends AbstractDb
{
    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * @var Role
     */
    private $roleResource;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\CompanyAccount\Model\ResourceModel\Customer $customerResource,
        \Amasty\CompanyAccount\Model\ResourceModel\Role $roleResource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->customerResource = $customerResource;
        $this->roleResource = $roleResource;
    }

    protected function _construct()
    {
        $this->_init(CompanyInterface::TABLE_NAME, CompanyInterface::COMPANY_ID);
    }

    /**
     * @param CompanyInterface $company
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyCustomerIds(CompanyInterface $company) : array
    {
        return $this->customerResource->getCustomerIdsByCompanyId($company->getCompanyId());
    }

    /**
     * @param \Amasty\CompanyAccount\Model\Company $company
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyRoleIds(\Amasty\CompanyAccount\Model\Company $company)
    {
        return $this->roleResource->getCompanyRoleIds($company);
    }

    /**
     * @param array $excludeCompanyIds
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function getAllSuperUserIds(array $excludeCompanyIds = [])
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), CompanyInterface::SUPER_USER_ID);

        if (!empty($excludeCompanyIds)) {
            $select->where('company_id not in (?)', $excludeCompanyIds);
        }

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return AbstractDb
     * @throws LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getOrigData(CompanyInterface::STATUS) !== null
            && $object->getOrigData(CompanyInterface::STATUS) != $object->getData(CompanyInterface::STATUS)
        ) {
            if ($object->isPending()) {
                throw new LocalizedException(__('Pending status is not available for an existed company'));
            }

            if ($object->isRejected()) {
                $object->setRejectedAt((new \DateTime())->getTimestamp());
            } else {
                $object->setData(CompanyInterface::REJECT_AT, null)
                    ->setData(CompanyInterface::REJECT_REASON, null);
            }
        }

        $object->setRestrictedPayments($object->getRestrictedPayments());

        return parent::_beforeSave($object);
    }

    /**
     * @param $companyId
     * @param $customerGroupId
     * @return $this
     * @throws LocalizedException
     */
    public function updateCompanyCustomerGroupId($companyId, $customerGroupId)
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            [CompanyInterface::CUSTOMER_GROUP_ID => $customerGroupId],
            sprintf('%s = %s', CompanyInterface::COMPANY_ID, $this->getConnection()->quote($companyId))
        );
        return $this;
    }
}
