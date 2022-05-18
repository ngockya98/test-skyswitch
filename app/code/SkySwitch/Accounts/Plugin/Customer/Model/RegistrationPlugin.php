<?php

namespace SkySwitch\Accounts\Plugin\Customer\Model;

use Magento\Customer\Model\Registration;

class RegistrationPlugin
{
    /**
     * Alter plugin for isAllowed method. Always return 'false' for check whether customers registration is allowed
     *
     * @param Registration $subject
     * @param boolean $result
     */
    public function afterIsAllowed(Registration $subject, $result)
    {
        return false;
    }
}
