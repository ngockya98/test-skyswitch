<?php

namespace SkySwitch\Orders\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use SkySwitch\Orders\Managers\OrderManager;
use Symfony\Component\Console\Input\InputOption;

class MigrateMacSerials extends Command
{
    const CSV_FILENAME = 'csv_filename'; //phpcs:ignore

    /**
     * @var OrderManager
     */
    protected $order_manager;

    /**
     * @var State
     */
    protected $state;

    /**
     * Set default console configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('skyswitch:orders:migratemacserials');
        $this->setDescription('Migrate MAC addresses and serial numbers from Store v1');
        $this->addOption(
            self::CSV_FILENAME,
            null,
            InputOption::VALUE_REQUIRED,
            'CSV Filename'
        );

        parent::configure();
    }

    /**
     * @param State $state
     * @param OrderManager $order_manager
     */
    public function __construct(State $state, OrderManager $order_manager)
    {
        $this->order_manager = $order_manager;
        $this->state = $state;

        parent::__construct();
    }

    /**
     * Execute console main method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->order_manager->migrateMacSerials($input->getOption(self::CSV_FILENAME));

        return $this;
    }
}
