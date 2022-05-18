<?php

namespace SkySwitch\Auth\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerAttributesResource extends AbstractDb
{
    const TABLE_NAME = 'sky_auth_customer_attributes'; // phpcs:ignore

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'customer_id');
    }

    /**
     * Get customer id using fusionAuth id
     *
     * @param int|string $fusionauth_id
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdByFusionAuthId($fusionauth_id): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select();

        $select->from($this->getMainTable())
            ->where('fusionauth_id = ?', $fusionauth_id);

        $row = $connection->fetchRow($select);

        return $row['customer_id'] ?? null;
    }
}
