<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Company;

class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countriesFactory;

    public function __construct(\Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory)
    {
        $this->countriesFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        return $this->countriesFactory->create()->toOptionArray();
    }
}
