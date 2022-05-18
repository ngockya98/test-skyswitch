<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Paypal\Model;

use Amasty\CompanyAccount\Model\Company\IsPaymentActiveForCurrentUser;
use Magento\Paypal\Model\Config;

class ConfigPlugin
{
    /**
     * @var IsPaymentActiveForCurrentUser
     */
    private $isPaymentActiveForCurrentUser;

    public function __construct(IsPaymentActiveForCurrentUser $isPaymentActiveForCurrentUser)
    {
        $this->isPaymentActiveForCurrentUser = $isPaymentActiveForCurrentUser;
    }

    /**
     * @param Config $subject
     * @param bool $result
     * @param string|null $methodCode
     * @return bool
     */
    public function afterIsMethodAvailable(Config $subject, bool $result, $methodCode = null): bool
    {
        $methodCode = $methodCode ?: $subject->getMethodCode();

        return $result && $this->isPaymentActiveForCurrentUser->execute($methodCode);
    }
}
