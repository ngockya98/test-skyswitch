<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Observer;

use Amasty\CompanyAccount\Model\Company\IsPaymentActiveForCurrentUser;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PaymentMethodIsActive implements ObserverInterface
{
    public const IS_AVAILABLE = 'is_available';

    /**
     * @var IsPaymentActiveForCurrentUser
     */
    private $isPaymentActiveForCurrentUser;

    public function __construct(IsPaymentActiveForCurrentUser $isPaymentActiveForCurrentUser)
    {
        $this->isPaymentActiveForCurrentUser = $isPaymentActiveForCurrentUser;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $methodInstance = $observer->getMethodInstance();

        $result = $observer->getResult()->getData(self::IS_AVAILABLE)
            && $this->isPaymentActiveForCurrentUser->execute($methodInstance->getCode());

        $observer->getResult()->setData(
            self::IS_AVAILABLE,
            $result
        );
    }
}
