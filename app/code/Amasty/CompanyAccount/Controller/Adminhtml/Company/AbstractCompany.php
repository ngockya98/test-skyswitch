<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Amasty\CompanyAccount\Model\Backend\Company\Registry as CompanyRegistry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Locale\FormatInterface;

abstract class AbstractCompany extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_CompanyAccount::company_management';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Amasty\CompanyAccount\Model\Repository\CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var CompanyRegistry
     */
    private $companyRegistry;

    /**
     * @var FormatInterface
     */
    private $formatNumber;

    public function __construct(
        Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Amasty\CompanyAccount\Model\Repository\CompanyRepository $companyRepository,
        CompanyRegistry $companyRegistry,
        FormatInterface $formatNumber
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->companyRepository = $companyRepository;
        $this->companyRegistry = $companyRegistry;
        $this->formatNumber = $formatNumber;
    }

    public function getCompanyRegistry(): CompanyRegistry
    {
        return $this->companyRegistry;
    }

    public function getFormatNumber(): FormatInterface
    {
        return $this->formatNumber;
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Amasty_CompanyAccount::company_accounts');
        $resultPage->getConfig()->getTitle()->prepend(__('Company Accounts'));

        return $resultPage;
    }
}
