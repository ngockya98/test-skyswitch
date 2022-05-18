<?php

namespace SkySwitch\Accounts\Plugin\Sales\Model\Order\Address;

use Magento\Sales\Model\Order\Address\Validator;
use Magento\Sales\Model\Order\Address;
use Magento\Customer\Model\Session as CustomerSession;

class ValidatorPlugin
{
    /**
     * @var CustomerSession
     */
    protected $customer_session;

    /**
     * @param CustomerSession $customer_session
     */
    public function __construct(CustomerSession $customer_session)
    {
        $this->customer_session = $customer_session;
    }

    /**
     * Before plugin for validate function. Automatic set customer logged email for address email if it empty
     *
     * @param Validator $subject
     * @param Address $address
     * @return void
     */
    public function beforeValidate(Validator $subject, Address $address)
    {
        if (empty($address->getEmail())) {
            $address->setEmail($this->customer_session->getCustomer()->getEmail());
        }
    }
}
