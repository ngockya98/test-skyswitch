<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup;

use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    private $customerResource;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->customerResource = $customerResource;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $this->customerResource->getConnection();
        $select = $connection->select()
            ->from($this->customerResource->getTable('customer_entity'), ['entity_id', 'is_active']);
        $customerStatuses = $connection->fetchPairs($select);

        $customers = [];
        foreach ($this->collectionFactory->create() as $customer) {
            $customers[] =
                [
                    CustomerInterface::CUSTOMER_ID => $customer->getId(),
                    CustomerInterface::STATUS => $customerStatuses[$customer->getId()] ?? 1
                ];
        }

        if ($customers) {
            $setup->getConnection()->insertMultiple(
                $setup->getTable(CustomerInterface::TABLE_NAME),
                $customers
            );
        }
    }
}
