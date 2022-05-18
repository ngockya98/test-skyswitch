<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CustomerInterface;
use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Amasty\CompanyAccount\Api\Data\PermissionInterface;

class Uninstall implements UninstallInterface
{
    public const TABLES_TO_DROP = [
        CompanyInterface::TABLE_NAME,
        CustomerInterface::TABLE_NAME,
        RoleInterface::TABLE_NAME,
        PermissionInterface::TABLE_NAME
    ];

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        foreach (self::TABLES_TO_DROP as $table) {
            $connection->dropTable(
                $installer->getTable($table)
            );
        }
    }
}
