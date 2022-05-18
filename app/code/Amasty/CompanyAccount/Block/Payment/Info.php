<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Block\Payment;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\CustomerDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info as PaymentInfo;

class Info extends PaymentInfo
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_CompanyAccount::payment/companycredit/info.phtml';

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    public function __construct(
        CustomerDataProvider $customerDataProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerDataProvider = $customerDataProvider;
    }

    public function getMethodTitle(): string
    {
        return $this->getMethod()->getConfigData('title', $this->getInfo()->getOrder()->getStoreId());
    }

    public function getCheckUrl(): string
    {
        return $this->getUrl('amcompany/company/edit', [
            CompanyInterface::COMPANY_ID => $this->getCompanyId(),
            '_fragment' => 'store_credit'
        ]);
    }

    /**
     * Get current company id.
     *
     * @return int|null
     * @throws LocalizedException
     */
    private function getCompanyId(): ?int
    {
        $customerId = (int) $this->getInfo()->getOrder()->getCustomerId();
        return $this->customerDataProvider->getCompanyIdByCustomerId($customerId);
    }
}
