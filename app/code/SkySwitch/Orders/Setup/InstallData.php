<?php //phpcs:ignore
namespace SkySwitch\Orders\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    //private $eavSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $sales_setup_factory;

    /**
     * @param SalesSetupFactory $sales_setup_factory
     */
    public function __construct(SalesSetupFactory $sales_setup_factory)
    {
        $this->sales_setup_factory = $sales_setup_factory;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $sales_setup = $this->sales_setup_factory->create(['setup' => $setup]);

        $sales_setup->addAttribute('order', 'distributor_order_number', [
            'type' => 'varchar',
            'length' => 25,
            'visible' => true,
            'required' => false,
            'grid' => true
        ]);

        $sales_setup->addAttribute('order', 'distributor_order_status', [
            'type' => 'varchar',
            'length' => 25,
            'visible' => true,
            'required' => false,
            'grid' => true
        ]);

        $sales_setup->addAttribute('order', 'distributor_name', [
            'type' => 'varchar',
            'length' => 25,
            'visible' => true,
            'required' => false,
            'grid' => true
        ]);

        $setup->endSetup();
    }
}
