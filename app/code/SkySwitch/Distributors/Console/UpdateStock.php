<?php

namespace SkySwitch\Distributors\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use SkySwitch\Distributors\Managers\DistributorManager;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\IndexerFactory;

class UpdateStock extends Command
{
    /**
     * @var DistributorManager
     */
    protected $distributor_manager;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var CollectionFactory
     */
    protected $indexer;

    /**
     * @var IndexerFactory
     */
    protected $index_factory;

    /**
     * Set default console configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('skyswitch:distributors:updatestock');
        $this->setDescription('Update distributor stock per product');

        parent::configure();
    }

    /**
     * @param State $state
     * @param DistributorManager $distributor_manager
     * @param CollectionFactory $indexer
     * @param IndexerFactory $index_factory
     */
    public function __construct(
        State $state,
        DistributorManager $distributor_manager,
        CollectionFactory $indexer,
        IndexerFactory $index_factory
    ) {
        $this->distributor_manager = $distributor_manager;
        $this->state = $state;
        $this->indexer = $indexer;
        $this->index_factory = $index_factory;

        parent::__construct();
    }

    /**
     * Execute console main method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->distributor_manager->updateStockPrice();

        $indexer_collection = $this->indexer->create();
        $index_ids = $indexer_collection->getAllIds();

        foreach ($index_ids as $index_id) {
            $index_id_array = $this->index_factory->create()->load($index_id);
            $index_id_array->reindexAll($index_id);
        }

        return $this;
    }
}
