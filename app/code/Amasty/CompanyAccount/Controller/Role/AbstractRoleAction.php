<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Role;

use Amasty\CompanyAccount\Api\RoleRepositoryInterface;

abstract class AbstractRoleAction extends \Amasty\CompanyAccount\Controller\AbstractAction
{
    /**
     * @var RoleRepositoryInterface
     */
    protected $roleRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        RoleRepositoryInterface $roleRepository
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->roleRepository = $roleRepository;
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        $roleId = (int)$this->getRequest()->getParam('role_id');
        $isValidRole = !$roleId;
        if ($roleId) {
            try {
                $currentRole = $this->roleRepository->getById($roleId);
                $company = $this->companyContext->getCurrentCompany();
                $rolesIds = $this->roleRepository->getRolesCollectionByCompanyId($company->getCompanyId())->getAllIds();
                $isValidRole = in_array($currentRole->getRoleId(), $rolesIds);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $isValidRole && parent::isAllowed();
    }
}
