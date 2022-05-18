<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Framework\Data\CollectionDataSourceInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract implements CollectionDataSourceInterface
{
    public const GENERAL_ALLOWED_GROUPS = 'general/allowed_groups';
    public const GENERAL_AUTO_APPROVE = 'general/auto_approve';
    public const GENERAL_INACTIVATE_CUSTOMER = 'general/inactivate_customer';
    public const GENERAL_URL_KEY = 'general/url_key';
    public const ADMIN_NOTIF_SENDER = 'admin_notif/sender';
    public const ADMIN_NOTIF_RECEIVER = 'admin_notif/receiver';
    public const ADMIN_NOTIF_NEW_COMPANY_REQUEST = 'admin_notif/new_company_request';
    public const ADMIN_NOTIF_NEW_COMPANY_CREATE = 'admin_notif/new_company_create';
    public const ADMIN_NOTIF_SALES_REPRESENTATIVE_APPOINTMENT = 'admin_notif/sales_representative_appointment';
    public const CUSTOMER_NOTIF_SENDER = 'customer_notif/sender';
    public const CUSTOMER_NOTIF_ACTIVE_STATUS = 'customer_notif/active_status';
    public const CUSTOMER_NOTIF_INACTIVE_STATUS = 'customer_notif/inactive_status';
    public const CUSTOMER_NOTIF_REJECTED_STATUS = 'customer_notif/rejected_status';
    public const CUSTOMER_NOTIF_CUSTOMER_LINKING = 'customer_notif/customer_linking';
    public const CUSTOMER_NOTIF_CUSTOMER_DISABLE = 'customer_notif/customer_disable';
    public const CUSTOMER_NOTIF_NEW_ADMIN = 'customer_notif/new_admin';
    public const CUSTOMER_NOTIF_ADMIN_UNASSIGN = 'customer_notif/admin_unassign';
    public const CUSTOMER_NOTIF_CREDIT_CHANGED = 'customer_notif/store_credit_changed';
    public const CUSTOMER_NOTIF_OVERDRAFT_CHANGED = 'customer_notif/overdraft_config_changed';
    public const CUSTOMER_NOTIF_OVERDRAFT_USED = 'customer_notif/overdraft_used';
    public const CUSTOMER_NOTIF_OVERDRAFT_PENALTY = 'customer_notif/overdraft_penalty_applied';

    /**
     * @var string
     */
    protected $pathPrefix = 'amasty_companyaccount/';

    /**
     * @return string
     */
    public function getAllowedCustomerGroups()
    {
        return $this->getValue(self::GENERAL_ALLOWED_GROUPS);
    }

    /**
     * @return bool
     */
    public function isAutoApprove()
    {
        return $this->isSetFlag(self::GENERAL_AUTO_APPROVE);
    }

    /**
     * @return string
     */
    public function getAdminSender()
    {
        return $this->getValue(self::ADMIN_NOTIF_SENDER);
    }

    /**
     * @return string
     */
    public function getAdminReceiver()
    {
        return $this->getValue(self::ADMIN_NOTIF_RECEIVER);
    }

    /**
     * @return string
     */
    public function getCustomerSender()
    {
        return $this->getValue(self::CUSTOMER_NOTIF_SENDER);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getEmailTemplate(string $path)
    {
        return $this->getValue($path);
    }

    /**
     * @return bool
     */
    public function getInactivateCustomerMode()
    {
        return (bool)$this->getValue(self::GENERAL_INACTIVATE_CUSTOMER);
    }

    public function getUrlKey(): string
    {
        return (string)$this->getValue(self::GENERAL_URL_KEY);
    }

    public function isShowPrefix(): bool
    {
        return (bool)$this->scopeConfig->getValue('customer/address/prefix_show');
    }

    public function isShowSuffix(): bool
    {
        return (bool)$this->scopeConfig->getValue('customer/address/suffix_show');
    }

    public function isVisibleCustomerPrefix(): bool
    {
        return $this->scopeConfig->getValue('customer/address/prefix_show') !== Nooptreq::VALUE_NO;
    }

    public function isVisibleCustomerMiddlename(): bool
    {
        return $this->scopeConfig->isSetFlag('customer/address/middlename_show');
    }

    public function isVisibleCustomerSuffix(): bool
    {
        return $this->scopeConfig->getValue('customer/address/suffix_show') !== Nooptreq::VALUE_NO;
    }
}
