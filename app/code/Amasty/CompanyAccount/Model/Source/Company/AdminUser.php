<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Source\Company;

use Magento\User\Model\ResourceModel\User\CollectionFactory as AdminUserCollectionFactory;

class AdminUser implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var AdminUserCollectionFactory
     */
    private $adminUserCollectionFactory;

    public function __construct(AdminUserCollectionFactory $adminUserCollectionFactory)
    {
        $this->adminUserCollectionFactory = $adminUserCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        $options = [];
        $adminUserCollection = $this->adminUserCollectionFactory->create();
        foreach ($adminUserCollection as $user) {
            $options[] = [
                'label' => $user->getFirstName() . ' ' . $user->getLastName(),
                'value' => $user->getId()
            ];
        }

        return $options;
    }
}
