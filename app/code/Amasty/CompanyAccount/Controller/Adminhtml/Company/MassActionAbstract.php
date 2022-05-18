<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Company;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Ui\Component\MassAction\Filter;
use Amasty\CompanyAccount\Model\ResourceModel\Company\CollectionFactory;
use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;

abstract class MassActionAbstract extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Amasty_CompanyAccount::company_management';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CompanyRepositoryInterface $companyRepository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->companyRepository = $companyRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $this->doAction($collection);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param AbstractDb $collection
     * @return void
     */
    abstract protected function doAction(AbstractDb $collection);
}
