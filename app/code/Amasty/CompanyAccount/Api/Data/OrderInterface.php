<?php

namespace Amasty\CompanyAccount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface OrderInterface extends ExtensibleDataInterface
{
    public const TABLE_NAME = 'amasty_company_account_order';
    public const COMPANY_ORDER_ID = 'company_order_id';
    public const COMPANY_ID = 'company_id';
    public const COMPANY_NAME = 'company_name';

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     *
     * @return \Amasty\CompanyAccount\Api\Data\OrderInterface
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getCompanyId();

    /**
     * @param int $companyId
     *
     * @return \Amasty\CompanyAccount\Api\Data\OrderInterface
     */
    public function setCompanyId($companyId);

    /**
     * @return string|null
     */
    public function getCompanyName();

    /**
     * @param string|null $companyName
     *
     * @return \Amasty\CompanyAccount\Api\Data\OrderInterface
     */
    public function setCompanyName($companyName);
}
