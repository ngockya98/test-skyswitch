<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup;

use Amasty\CompanyAccount\Setup\Operation\InsertCompanyOrders;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var InsertCompanyOrders
     */
    private $insertCompanyOrders;

    public function __construct(
        InsertCompanyOrders $insertCompanyOrders
    ) {
        $this->insertCompanyOrders = $insertCompanyOrders;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->insertCompanyOrders->execute($setup);
        }
    }
}
