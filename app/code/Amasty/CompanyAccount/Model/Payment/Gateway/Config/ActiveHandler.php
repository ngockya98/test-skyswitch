<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model\Payment\Gateway\Config;

use Amasty\CompanyAccount\Model\CompanyContext;
use Amasty\CompanyAccount\Model\Payment\ConfigProvider;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

class ActiveHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    public function __construct(
        ConfigInterface $configInterface,
        CustomerRepositoryInterface $customerRepository,
        CompanyContext $companyContext,
        SubjectReader $subjectReader,
        UserContextInterface $userContext
    ) {
        $this->configInterface = $configInterface;
        $this->customerRepository = $customerRepository;
        $this->companyContext = $companyContext;
        $this->subjectReader = $subjectReader;
        $this->userContext = $userContext;
    }

    /**
     * @param array $subject
     * @param null|int $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function handle(array $subject, $storeId = null)
    {
        $configValue = $this->configInterface->getValue($this->subjectReader->readField($subject), $storeId);

        if (in_array($this->userContext->getUserType(), [
            UserContextInterface::USER_TYPE_ADMIN,
            UserContextInterface::USER_TYPE_INTEGRATION
        ])) {
            return (bool) $configValue;
        }

        $customerId = $this->companyContext->getCurrentCustomerId();
        if ($configValue && $customerId) {
            $customer = $this->getCustomer($subject, (int) $customerId);
            if (!$customer) {
                return false;
            }
            if ($customer->getExtensionAttributes() !== null
                && $customer->getExtensionAttributes()->getAmcompanyAttributes() !== null
            ) {
                return $customer->getExtensionAttributes()->getAmcompanyAttributes()->getStatus()
                    && $this->companyContext->isResourceAllow(ConfigProvider::ACL_RESOURCE);
            }
        }

        return false;
    }

    /**
     * @param int $customerId
     * @return CustomerInterface|null
     * @throws LocalizedException
     */
    private function retrieveCustomer(int $customerId): ?CustomerInterface
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get customer entity from Company context or from payment quote.
     *
     * @param array $subject
     * @param int $customerId
     * @return CustomerInterface|bool
     * @throws LocalizedException
     */
    private function getCustomer(array $subject, int $customerId): ?CustomerInterface
    {
        $customer = $this->retrieveCustomer($customerId);
        if (!$customer && !empty($subject['payment']) && $subject['payment'] instanceof PaymentDataObjectInterface) {
            /** @var PaymentDataObjectInterface $payment */
            $payment = $subject['payment'];
            /** @var CustomerInterface $customer */
            $customer = $this->retrieveCustomer((int) $payment->getOrder()->getCustomerId());
            if (!$customer) {
                return null;
            }
        }

        return $customer;
    }
}
