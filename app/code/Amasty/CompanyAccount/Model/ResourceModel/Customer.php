<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\ResourceModel;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\ConfigProvider;
use Amasty\CompanyAccount\Model\MailManager;
use Amasty\CompanyAccount\Model\Source\Company\Group;
use Amasty\CompanyAccount\Model\Source\Customer\Status;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Framework\Indexer\IndexerRegistry;

class Customer extends AbstractDb
{
    public const CUSTOMER_ENTITY_TABLE = 'customer_entity';

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Role
     */
    private $roleResource;

    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Role $roleResource,
        MailManager $mailManager,
        IndexerRegistry $indexerRegistry,
        ConfigProvider $configProvider,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->roleResource = $roleResource;
        $this->mailManager = $mailManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->configProvider = $configProvider;
    }

    protected function _construct()
    {
        $this->_init(CustomerInterface::TABLE_NAME, CustomerInterface::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerExtensionAttributes($customerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId)
            ->limit(1);

        return $connection->fetchRow($select);
    }

    /**
     * @param CustomerInterface $customerExtension
     * @return $this
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAdvancedCustomerAttributes(CustomerInterface $customerExtension)
    {
        $customerExtensionData = $this->_prepareDataForSave($customerExtension);
        if ($customerExtensionData) {
            try {
                if (isset($customerExtensionData[CustomerInterface::COMPANY_ID])) {
                    $customerExtensionData[CustomerInterface::COMPANY_ID] =
                        $customerExtensionData[CustomerInterface::COMPANY_ID] ?: null;
                }
                $this->getConnection()->insertOnDuplicate(
                    $this->getMainTable(),
                    $customerExtensionData,
                    array_keys($customerExtensionData)
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There was an error saving customer.'));
            }
        }

        return $this;
    }

    /**
     * @param int $companyId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdsByCompanyId($companyId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getMainTable()], ['customer_id'])
            ->joinInner(
                ['company' => $this->getTable(CompanyInterface::TABLE_NAME)],
                'company.company_id = main_table.company_id',
                []
            )
            ->where('main_table.company_id = ?', $companyId)
            ->where('company.super_user_id != main_table.customer_id');

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param int $customerId
     * @return int|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyIdByCustomerId($customerId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getMainTable()], ['company_id'])
            ->joinInner(
                ['company' => $this->getTable(CompanyInterface::TABLE_NAME)],
                'company.company_id = main_table.company_id',
                []
            )
            ->where('customer_id = ?', $customerId);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $groupId
     * @param array $customerIds
     * @param bool $useCompanyGroup
     * @return $this
     */
    public function updateCustomerGroup($groupId, $customerIds = [], $useCompanyGroup = false)
    {
        if ($groupId && $useCompanyGroup && !empty($customerIds)) {
            $this->getConnection()->update(
                $this->getTable(self::CUSTOMER_ENTITY_TABLE),
                ['group_id' => $groupId],
                ['entity_id in (?)' => $customerIds]
            );

            $indexer = $this->indexerRegistry->get(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID);
            $indexer->reindexList($customerIds);
        }

        return $this;
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function getGroupIdByCustomerId($customerId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(self::CUSTOMER_ENTITY_TABLE), ['group_id'])
            ->where('entity_id = ?', $customerId);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param array $customerIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function disableCustomers($customerIds = [])
    {
        if (!empty($customerIds)) {
            $this->getConnection()->update(
                $this->getTable(CustomerInterface::TABLE_NAME),
                [CustomerInterface::STATUS => Status::INACTIVE],
                ['customer_id in (?)' => $customerIds]
            );

            foreach ($customerIds as $customerId) {
                $this->mailManager->sendCustomerEmails(
                    [[CustomerInterface::CUSTOMER_ID => $customerId]],
                    MailManager::DISABLE_CUSTOMER
                );
            }
        }

        return $this;
    }

    /**
     * @param array $customerIds
     * @return $this
     */
    public function enableCustomers($customerIds = [])
    {
        if (!empty($customerIds)) {
            $this->getConnection()->update(
                $this->getTable(CustomerInterface::TABLE_NAME),
                [CustomerInterface::STATUS => Status::ACTIVE],
                ['customer_id in (?)' => $customerIds]
            );
        }

        return $this;
    }

    /**
     * @param int $companyId
     * @param array $customerIds
     * @param bool $useDefaultRole
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignCompany($companyId, $customerIds = [], $useDefaultRole = true)
    {
        $roleId = $useDefaultRole ? $this->roleResource->getDefaultUserRoleId($companyId) : null;
        $currentCustomers = $this->getCustomerIdsByCompanyId($companyId);
        $insertData = [];
        $newCustomers = [];
        foreach ($customerIds as $customerId) {
            $data = [
                CustomerInterface::CUSTOMER_ID => $customerId,
                CustomerInterface::COMPANY_ID => $companyId,
                CustomerInterface::JOB_TITLE => '',
                CustomerInterface::ROLE_ID => $roleId
            ];
            if (!in_array($customerId, $currentCustomers)) {
                $newCustomers[] = $data;
            }
            $insertData[] = $data;
        }
        if ($newCustomers) {
            $this->mailManager->sendCustomerEmails($newCustomers, MailManager::LINK_CUSTOMER);
        }
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $insertData);
        $this->enableCustomers($customerIds);

        return $this;
    }

    /**
     * @param array $customerIds
     * @param bool $forceDisable
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unassignCompany($customerIds = [], $forceDisable = true)
    {
        if ($this->configProvider->getInactivateCustomerMode() && $forceDisable) {
            $this->disableCustomers($customerIds);
        }
        $this->unassignCustomers($customerIds);

        return $this;
    }

    /**
     * @param array $customerIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unassignCustomers($customerIds = [])
    {
        if (!empty($customerIds)) {
            $this->getConnection()->update(
                $this->getTable(CustomerInterface::TABLE_NAME),
                [CustomerInterface::COMPANY_ID => null],
                ['customer_id in (?)' => $customerIds]
            );
        }

        return $this;
    }
}
