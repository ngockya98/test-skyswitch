<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Role;

use Amasty\CompanyAccount\Api\Data\PermissionInterface;
use Amasty\CompanyAccount\Api\Data\PermissionInterfaceFactory;
use Amasty\CompanyAccount\Api\Data\RoleInterface;
use Amasty\CompanyAccount\Api\Data\RoleInterfaceFactory;
use Amasty\CompanyAccount\Api\RoleRepositoryInterface;
use Amasty\CompanyAccount\Block\Roles\Grid;
use Amasty\CompanyAccount\Model\Repository\PermissionRepository;
use Magento\Framework\Exception\LocalizedException;

class SaveRole extends AbstractRoleAction
{
    public const RESOURCE = 'Amasty_CompanyAccount::roles_add';

    /**
     * @var RoleInterfaceFactory
     */
    private $roleFactory;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * @var PermissionInterfaceFactory
     */
    private $permissionFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect
     */
    private $resultRedirect;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        RoleRepositoryInterface $roleRepository,
        RoleInterfaceFactory $roleFactory,
        PermissionRepository $permissionRepository,
        PermissionInterfaceFactory $permissionFactory
    ) {
        parent::__construct($context, $companyContext, $logger, $roleRepository);
        $this->roleFactory = $roleFactory;
        $this->permissionRepository = $permissionRepository;
        $this->permissionFactory = $permissionFactory;
        $this->resultRedirect = $this->resultRedirectFactory->create();
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $this->initResultRedirect($data);
        if (!$data) {
            return $this->resultRedirect;
        }
        try {
            $successMessage = isset($data[RoleInterface::ROLE_ID])
                ? __('The role was updated successfully.')
                : __('The role was created successfully.');
            $this->validateData($data);
            $this->createRole($data);
            $this->messageManager->addSuccessMessage($successMessage);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred on the server. Your changes have not been saved.')
            );
            $this->logger->critical($e);
            return $this->resultRedirect;
        }

        $this->resultRedirect->setPath('*/*/');

        return $this->resultRedirect;
    }

    /**
     * @param array $params
     */
    private function initResultRedirect(array $params)
    {
        if (isset($params[RoleInterface::ROLE_ID])) {
            $this->resultRedirect->setPath(
                Grid::AMASTY_COMPANY_ROLE_EDIT,
                [RoleInterface::ROLE_ID => $params[RoleInterface::ROLE_ID]]
            );
        } else {
            $this->resultRedirect->setPath('*/*/create');
        }
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    protected function validateData(array $data)
    {
        $name = $data[RoleInterface::ROLE_NAME] ?? '';
        if (!$name) {
            throw new LocalizedException(__('Your changes have not been saved. Please enter a role title'));
        }
        $permissions = $data['role_permissions'] ?? [];
        if (!$permissions) {
            throw new LocalizedException(__('Your changes have not been saved. Please choose role permissions'));
        }
    }

    /**
     * @param array $data
     */
    private function createRole(array $data)
    {
        if (isset($data[RoleInterface::ROLE_NAME])) {
            $role = $this->roleFactory->create();
            $company = $this->companyContext->getCurrentCompany();
            $role->setCompanyId($company->getCompanyId());
            $role->setRoleName($data[RoleInterface::ROLE_NAME]);
            $role->setRoleId($data[RoleInterface::ROLE_ID] ?? null);
            $this->roleRepository->save($role);
            $this->savePermissions($role);
        }
    }

    /**
     * @param RoleInterface $role
     */
    private function savePermissions(RoleInterface $role)
    {
        $permissions = $this->getRequest()->getParam('role_permissions');
        $permissions = $permissions ? explode(',', $permissions) : [];

        $data = [];
        foreach ($permissions as $permission) {
            $data[] = [
                PermissionInterface::ROLE_ID => $role->getRoleId(),
                PermissionInterface::RESOURCE_ID => $permission
            ];
        }
        $this->permissionRepository->multipleSave($data);
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->companyContext->isCurrentUserCompanyUser()
            && $this->companyContext->isActiveOrInactiveCompany()
            && parent::isAllowed();
    }
}
