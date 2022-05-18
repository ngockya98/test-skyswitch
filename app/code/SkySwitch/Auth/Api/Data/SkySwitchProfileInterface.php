<?php

namespace SkySwitch\Auth\Api\Data;

interface SkySwitchProfileInterface
{
    /**
     * Get Account Id method
     *
     * @return mixed
     */
    public function getAccountId();

    /**
     * Get Account name method
     *
     * @return mixed
     */
    public function getAccountName();

    /**
     * Get Account number method
     *
     * @return mixed
     */
    public function getAccountNumber();

    /**
     * Get email method
     *
     * @return mixed
     */
    public function getEmail();

    /**
     * Get Permissions method
     *
     * @return mixed
     */
    public function getPermissions();
}
