<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Model\MailManager;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class SendCreditEmail extends Action
{
    /**
     * @var MailManager
     */
    private $mailManager;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        MailManager $mailManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->mailManager = $mailManager;
        $this->companyRepository = $companyRepository;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $companyId = (int) $this->getRequest()->getParam('company_id');
        $isExceed = (bool) $this->getRequest()->getParam('exceed');

        try {
            $company = $this->companyRepository->getById($companyId, true);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This Company no longer exists.'));
        }

        try {
            if ($isExceed) {
                $this->mailManager->sendOverdraftPenalty($company->getExtensionAttributes()->getCredit());
            } else {
                $this->mailManager->sendOverdraftUsed($company->getExtensionAttributes()->getCredit());
            }
            $this->messageManager->addSuccessMessage(__('Email was send successfully.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/edit', ['company_id' => $companyId]);
    }
}
