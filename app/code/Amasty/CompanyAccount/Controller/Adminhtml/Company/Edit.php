<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Magento\Framework\Controller\ResultFactory;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;

class Edit extends AbstractCompany
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $companyId = (int)$this->getRequest()->getParam(CompanyInterface::COMPANY_ID);

        try {
            /**
             * @var \Amasty\CompanyAccount\Api\Data\CompanyInterface $model
             */
            if ($companyId) {
                $model = $this->companyRepository->getById($companyId, true);
            } else {
                $model = $this->companyRepository->getNew(true);
            }
            $this->getCompanyRegistry()->set($model);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage(__('This Company no longer exists.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $companyName = $model->getCompanyName() ?: $model->getLegalName();

        $text = $companyName ? __('Edit Company "%1"', $companyName) : __('New Company');
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend($text);

        return $resultPage;
    }
}
