<?php

namespace SkySwitch\Auth\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CustomerAttributesInterface extends ExtensibleDataInterface
{
    const CUSTOMER_ID = 'customer_id'; // phpcs:ignore
    const FUSIONAUTH_ID = 'fusionauth_id'; // phpcs:ignore
    const RESELLER_ID = 'reseller_id'; // phpcs:ignore

    /**
     * Get Customer Id method
     *
     * @return mixed
     */
    public function getCustomerId();

    /**
     * Get Fusion Auth Id method
     *
     * @return mixed
     */
    public function getFusionAuthId();

    /**
     * Get Reseller Id method
     *
     * @return mixed
     */
    public function getResellerId();
}
