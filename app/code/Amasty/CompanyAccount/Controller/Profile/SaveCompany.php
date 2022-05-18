<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller\Profile;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Controller\AbstractAction;
use Amasty\CompanyAccount\Model\Source\Company\Status as CompanyStatus;
use Magento\Framework\Exception\LocalizedException;

class SaveCompany extends AbstractAction
{
    public const SESSION_NAME = 'Amasty_CompanyAccount_Data';
    public const AMASTY_COMPANY_PROFILE_INDEX = 'amasty_company/profile/index';
    public const REDIRECT_URL = 'amasty_company/profile/create';

    /**
     * @var \Amasty\CompanyAccount\Api\Data\CompanyInterfaceFactory
     */
    private $companyFactory;

    /**
     * @var \Amasty\CompanyAccount\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Amasty\CompanyAccount\Api\Data\CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;

    /**
     * @var \Amasty\CompanyAccount\Model\ResourceModel\Customer
     */
    private $customer;

    /**
     * @var \Amasty\CompanyAccount\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @var string
     */
    protected $redirectUrl = self::REDIRECT_URL;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\CompanyAccount\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\CompanyAccount\Api\Data\CompanyInterfaceFactory $companyFactory,
        \Amasty\CompanyAccount\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory,
        \Amasty\CompanyAccount\Api\CompanyRepositoryInterface $companyRepository,
        \Amasty\CompanyAccount\Model\ResourceModel\Customer $customer,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amasty\CompanyAccount\Model\ConfigProvider $configProvider,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->companyFactory = $companyFactory;
        $this->companyRepository = $companyRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customer = $customer;
        $this->configProvider = $configProvider;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->session = $session;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParam('company', []);
        if ($data) {
            $this->saveCompany($data);
        }

        return $this->resultRedirectFactory->create()->setPath($this->redirectUrl);
    }

    /**
     * @param array $data
     */
    protected function saveCompany(array $data)
    {
        $this->session->setData(self::SESSION_NAME, $data);
        try {
            $this->validateEmail($data);
            $company = $this->companyFactory->create();
            $company->setData($data);
            $this->prepareCompanyData($company);
            $this->companyRepository->save($company);
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($data));
            $this->redirectUrl = self::AMASTY_COMPANY_PROFILE_INDEX;
            $this->session->setData(self::SESSION_NAME, []);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred on the server. Your changes have not been saved.')
            );
            $this->logger->critical($e);
        }
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    protected function validateEmail(array $data)
    {
        if (isset($data[CompanyInterface::COMPANY_EMAIL])
            && $this->isEmailExist($data[CompanyInterface::COMPANY_EMAIL])
            && !isset($data[CompanyInterface::COMPANY_ID])
        ) {
            throw new LocalizedException(
                __('There is already a company account associated with this email address.'
                    . ' Please enter a different email address.')
            );
        }
    }

    /**
     * @param array $data
     * @return \Magento\Framework\Phrase
     */
    private function getSuccessMessage(array $data)
    {
        if (isset($data['company_id'])) {
            $message =  __('Company Account information was successfully saved.');
        } else {
            $message = $this->configProvider->isAutoApprove()
                ? __('Thank you! New Company Account was created successfully.')
                : __('Thank you! Your request is received and will be reviewed as soon as possible.');
        }

        return $message;
    }

    /**
     * @param CompanyInterface $company
     */
    private function prepareCompanyData(CompanyInterface $company)
    {
        if (!$company->getCompanyId()) {
            $company->setSuperUserId($this->companyContext->getCurrentCustomerId());
            $company->setCustomerGroupId($this->companyContext->getCurrentCustomerGroupId());
            $this->setStatus($company);
            $userCollection = $this->userCollectionFactory->create();
            $userCollection->getSelect()->limit(1);
            $company->setSalesRepresentativeId($userCollection->getFirstItem()->getId());
        }

        $this->setStreet($company);
    }

    /**
     * @param CompanyInterface $company
     */
    private function setStreet(CompanyInterface $company)
    {
        $street = $company->getStreet();
        if (is_array($street) && count($street)) {
            $company->setStreet(trim(implode("\n", $street)));
        }
    }

    /**
     * @param CompanyInterface $company
     */
    private function setStatus(CompanyInterface $company)
    {
        if ($this->configProvider->isAutoApprove()) {
            $company->setStatus(CompanyStatus::STATUS_ACTIVE);
        } else {
            $company->setStatus(CompanyStatus::STATUS_PENDING);
        }
    }

    /**
     * @param string $email
     * @return bool
     */
    private function isEmailExist($email)
    {
        $company = $this->companyRepository->getByField(CompanyInterface::COMPANY_EMAIL, $email);

        return (bool)$company->getCompanyId();
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return !$this->companyContext->isCurrentUserCompanyUser() && $this->companyContext->isAllowedCustomerGroup();
    }
}
