<?php
namespace SkySwitch\CustomTheme\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class InsertCategory implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var CategoryFactory */
    protected $categoryFactory;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface    $moduleDataSetup,
        CategoryFactory             $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface       $storeManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;

        // Create Base Category Model for Site
        $storeId = $this->storeManager->getStore()->getStoreId();
        $defaultCategoryId = 2;
        $insertData = $this->getCategoryDefaultData();

        if (count($insertData) > 0) {
            foreach ($insertData as $catName) {
                if ($catName) {
                    $category = $this->categoryFactory->create();
                    $catExist = $category->getCollection()
                        ->addAttributeToFilter('name', $catName)
                        ->getFirstItem();

                    if (!$catExist->getId()) {
                        $category = $this->categoryFactory->create();
                        $category->setName($catName);
                        $category->setIsActive(true);
                        $category->setIsAnchor(true);
                        $category->setParentId($defaultCategoryId);
                        $category->setStoreId($storeId);
                        $this->categoryRepository->save($category);
                    }
                }
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.0.0';
    }





    /**
     * Defined default category array
     *
     * @return string[]
     */
    private function getCategoryDefaultData(){
        return $data = ['3CX', 'Algo', 'Amcrest Cameras', 'Boom Collaboration', 'Conference Phones', 'CyberData',
            'Dialplate', 'Fanvil', 'Grandstream', 'Infiot Edge', 'Jabra', 'Meeting Manager', 'Netgear', 'Poly',
            'Poly Plantronics Headsets', 'Portable Speakerphones', 'Power Supplies', 'Redstone', 'Ribbon (Edgemarc)',
            'SNOM', 'Yealink (Add Power Separately For 888VoIP/TeleDynamics)', 'Non-Platform Devices'];
    }
}
