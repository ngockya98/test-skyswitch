<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Extensions\Credit;

use Amasty\CompanyAccount\Api\CreditRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var CreditRepositoryInterface
     */
    private $creditRepository;

    public function __construct(CreditRepositoryInterface $creditRepository)
    {
        $this->creditRepository = $creditRepository;
    }

    /**
     * @param CompanyInterface $entity
     * @param array $arguments
     * @return CompanyInterface
     */
    public function execute($entity, $arguments = [])
    {
        try {
            $credit = $this->creditRepository->getByCompanyId($entity->getCompanyId());
        } catch (NoSuchEntityException $e) {
            $credit = $this->creditRepository->getNew();
            $credit->setCompanyId($entity->getCompanyId());
        }

        $entity->getExtensionAttributes()->setCredit($credit);

        return $entity;
    }
}
