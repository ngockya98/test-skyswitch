<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Setup;

use Amasty\CompanyAccount\Setup\Operation\AddCompanyOrders;
use Amasty\CompanyAccount\Setup\Operation\AddCompanyPaymentsField;
use Amasty\CompanyAccount\Setup\Operation\AddCreditEventTable;
use Amasty\CompanyAccount\Setup\Operation\AddCreditTable;
use Amasty\CompanyAccount\Setup\Operation\AddOverdraftTable;
use Amasty\CompanyAccount\Setup\Operation\AddUseCompanyGroupField;
use Amasty\CompanyAccount\Setup\Operation\AddUserStatusField;
use Amasty\CompanyAccount\Setup\Operation\DeleteForeignKey;
use Amasty\CompanyAccount\Setup\Operation\RestoreCompanyForeignKey;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var AddCompanyOrders
     */
    private $addCompanyOrders;

    /**
     * @var AddCompanyPaymentsField
     */
    private $addCompanyPaymentsField;

    /**
     * @var AddUserStatusField
     */
    private $addUserStatusField;

    /**
     * @var AddUseCompanyGroupField
     */
    private $addUseCompanyGroupField;

    /**
     * @var DeleteForeignKey
     */
    private $deleteForeignKey;

    /**
     * @var AddCreditEventTable
     */
    private $addCreditEventTable;

    /**
     * @var AddCreditTable
     */
    private $addCreditTable;

    /**
     * @var AddOverdraftTable
     */
    private $addOverdraftTable;

    /**
     * @var RestoreCompanyForeignKey
     */
    private $restoreCompanyForeignKey;

    public function __construct(
        AddCompanyPaymentsField $addCompanyPaymentsField,
        AddCompanyOrders $addCompanyOrders,
        AddUserStatusField $addUserStatusField,
        AddUseCompanyGroupField $addUseCompanyGroupField,
        DeleteForeignKey $deleteForeignKey,
        AddCreditTable $addCreditTable,
        AddCreditEventTable $addCreditEventTable,
        AddOverdraftTable $addOverdraftTable,
        RestoreCompanyForeignKey $restoreCompanyForeignKey
    ) {
        $this->addCompanyPaymentsField = $addCompanyPaymentsField;
        $this->addCompanyOrders = $addCompanyOrders;
        $this->addUserStatusField = $addUserStatusField;
        $this->addUseCompanyGroupField = $addUseCompanyGroupField;
        $this->deleteForeignKey = $deleteForeignKey;
        $this->addCreditTable = $addCreditTable;
        $this->addCreditEventTable = $addCreditEventTable;
        $this->addOverdraftTable = $addOverdraftTable;
        $this->restoreCompanyForeignKey = $restoreCompanyForeignKey;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addCompanyPaymentsField->execute($setup);
            $this->addCompanyOrders->execute($setup);
            $this->addUserStatusField->execute($setup);
            $this->addUseCompanyGroupField->execute($setup);
            $this->deleteForeignKey->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addCreditTable->execute($setup);
            $this->addCreditEventTable->execute($setup);
            $this->addOverdraftTable->execute($setup);
            $this->restoreCompanyForeignKey->execute($setup);
        }

        $setup->endSetup();
    }
}
