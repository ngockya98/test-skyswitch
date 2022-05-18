<?php
namespace SkySwitch\Distributors\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\RequestInterface;
use SkySwitch\Distributors\Model\DistributorFactory;

class Eav extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var DistributorFactory
     */
    protected $distributor_factory;

    /**
     * Eav constructor.
     * @param LocatorInterface $locator
     * @param RequestInterface $request
     * @param DistributorFactory $distributor_factory
     */
    public function __construct(
        LocatorInterface $locator,
        RequestInterface $request,
        DistributorFactory $distributor_factory
    ) {
        $this->locator = $locator;
        $this->request = $request;
        $this->distributor_factory = $distributor_factory;
    }

    /**
     * ModifyData
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * ModifyMeta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $enabled_distributors = explode(',', $this->locator->getProduct()->getData('distributors'));

        $result = $this->distributor_factory->create();
        $distributors = $result->getCollection()->getData();

        $disabled_distributors = array_filter($distributors, function ($dist) use ($enabled_distributors) {
            return !in_array($dist['distributor_id'], $enabled_distributors);
        });

        $disabled_groups = array_map(function ($dist) {
            return strtolower($dist['name']);
        }, $disabled_distributors);

        foreach ($disabled_groups as $disabled_group) {
            unset($meta[$disabled_group]);
        }

        return $meta;
    }
}
