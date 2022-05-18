<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Plugin\Amazon\Core\Helper;

use Amasty\CompanyAccount\Model\Company\IsPaymentActiveForCurrentUser;
use Amasty\CompanyAccount\Plugin\Amazon\Payment\Gateway\Config\ConfigPlugin;
use Amazon\Core\Helper\Data;

class DataPlugin
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
     * @param Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsPaymentButtonEnabled(Data $subject, $result): bool
    {
        return $result && $this->isPaymentActiveForCurrentUser->execute(ConfigPlugin::CODE);
    }

    /**
     * @param Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsLoginButtonEnabled(Data $subject, $result): bool
    {
        return $result && $this->isPaymentActiveForCurrentUser->execute(ConfigPlugin::CODE);
    }
}
