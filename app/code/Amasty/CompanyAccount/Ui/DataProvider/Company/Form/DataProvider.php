<?php

declare(strict_types=1);

namespace  Amasty\CompanyAccount\Ui\DataProvider\Company\Form;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\OverdraftInterface;
use Amasty\CompanyAccount\Api\OverdraftRepositoryInterface;
use Amasty\CompanyAccount\Model\RegistryConstants;
use Amasty\CompanyAccount\Model\ResourceModel\Company\CollectionFactory;
use Amasty\CompanyAccount\Model\ResourceModel\Customer\Grid\CollectionFactory as CustomerGridCollectionFactory;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CustomerGridCollectionFactory
     */
    private $gridCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $customerNameGeneration;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var OverdraftRepositoryInterface
     */
    private $overdraftRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CompanyRepositoryInterface $companyRepository,
        CustomerGridCollectionFactory $gridCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerNameGenerationInterface $customerNameGeneration,
        RegionFactory $regionFactory,
        PaymentHelper $paymentHelper,
        DateTime $date,
        OverdraftRepositoryInterface $overdraftRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->date = $date;
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->companyRepository = $companyRepository;
        $this->gridCollectionFactory = $gridCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->customerNameGeneration = $customerNameGeneration;
        $this->regionFactory = $regionFactory;
        $this->paymentHelper = $paymentHelper;
        $this->overdraftRepository = $overdraftRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData()
    {
        $data = parent::getData();
        if ($data['totalRecords'] > 0) {
            if (isset($data['items'][0][CompanyInterface::COMPANY_ID])) {
                $companyId = (int)$data['items'][0][CompanyInterface::COMPANY_ID];
                $company = $this->companyRepository->getById($companyId);
                $data = [$companyId => $company->getData()];
                $data[$companyId]['customer_ids'] = $this->getCustomerData($company);
                $data[$companyId]['store_credit'] = $this->getStoreCreditData(
                    $company->getExtensionAttributes()->getCredit()->getData()
                );

                if ($company->getRegionId()) {
                    $region = $this->regionFactory->create()->load($company->getRegionId());
                    $data[$companyId]['region'] = $region->getName();
                }

                if (!$company->getRejectAt()) {
                    $data[$companyId]['rejected_at'] = $this->date->gmtDate();
                }

                if ($company->getSuperUserId()) {
                    try {
                        /**
                         * @var Customer $customer
                         */
                        $customer = $this->customerRepository->getById($company->getSuperUserId());
                        $superUserName = $this->customerNameGeneration->getCustomerName($customer);
                        $data[$companyId]['super_user_name'] = $superUserName;
                        $data[$companyId]['super_user_ids'] = [[
                            'entity_id' => $company->getSuperUserId(),
                            'name' => $superUserName
                        ]];
                    } catch (NoSuchEntityException $e) {
                        $data[$companyId]['super_user_id'] = null;
                    }
                }

                if ($company->getRestrictedPayments()) {
                    $data[$companyId][CompanyInterface::RESTRICTED_PAYMENTS] = $company->getRestrictedPayments(true);
                }

            }
        }

        if ($savedData = $this->dataPersistor->get(RegistryConstants::COMPANY_DATA)) {
            $savedCompanyId = $savedData[CompanyInterface::COMPANY_ID] ?? null;
            if (isset($data[$savedCompanyId])) {
                $data[$savedCompanyId] = array_merge($data[$savedCompanyId], $savedData);
            } else {
                $data[$savedCompanyId] = $savedData;
            }
            $this->dataPersistor->clear(RegistryConstants::COMPANY_DATA);
        }

        return $data;
    }

    /**
     * @param CompanyInterface $company
     * @return array
     */
    private function getCustomerData(CompanyInterface $company)
    {
        $customerCollection = $this->gridCollectionFactory->create();

        $customerCollection->addCompanyDataToSelect()
            ->addCustomerIdFilter($this->getCompanyCustomerIds($company));
        $result = [];

        foreach ($customerCollection->getItems() as $customer) {
            $result[] = $this->fillData($customer);
        }

        return ['company_user_container' => $result];
    }

    /**
     * @param CompanyInterface $company
     * @return array
     */
    private function getCompanyCustomerIds(CompanyInterface $company)
    {
        $companyCustomerIds = $company->getCustomerIds();

        foreach ($companyCustomerIds as $index => $customerId) {
            if ($customerId == $company->getSuperUserId()) {
                unset($companyCustomerIds[$index]);
            }
        }

        return $companyCustomerIds;
    }

    /**
     * @param $customer
     *
     * @return array
     */
    private function fillData($customer)
    {
        return [
            'entity_id' => $customer->getId(),
            'name'      => $customer->getName(),
            'email'     => $customer->getEmail(),
            'role'      => $customer->getRole(),
            'status'    => $customer->getStatus()
        ];
    }

    private function getStoreCreditData(array $creditData): array
    {
        if (isset($creditData['id'])) {
            $creditId = (int) $creditData['id'];
            if ($this->overdraftRepository->isExistForCredit($creditId)) {
                $isOverdraftExceed = $this->overdraftRepository->isOverdraftExceed($creditId);
                $creditData['overdraft']['exceed'] = $isOverdraftExceed;
                if (!$isOverdraftExceed) {
                    $overdraft = $this->overdraftRepository->getByCreditId($creditId);
                    $creditData['overdraft'][OverdraftInterface::REPAY_DATE] = $overdraft->getRepayDate();
                }
            }
        }

        return $creditData;
    }
}
