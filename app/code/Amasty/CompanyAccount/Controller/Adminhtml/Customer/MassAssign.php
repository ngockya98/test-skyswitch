<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Adminhtml\Customer;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Zend\Stdlib\Exception\LogicException;

class MassAssign extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Amasty_CompanyAccount::company_management';
    public const PARAM_NAME = 'company_id';

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
        $customerIds = $collection->getAllIds();
        $companyId = (int)$this->getRequest()->getParam(self::PARAM_NAME);
        try {
            $company = $this->companyRepository->getById($companyId);
            $company->addCustomerIds($customerIds);
            $this->companyRepository->save($company);
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been saved.', count($customerIds))
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This Company no longer exists.'));
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('customer/index/');
    }
}
