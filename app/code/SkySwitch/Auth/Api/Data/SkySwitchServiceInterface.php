<?php

namespace SkySwitch\Auth\Api\Data;

interface SkySwitchServiceInterface
{
    /**
     * Get profile method
     *
     * @param int $reseller_id
     * @return SkySwitchProfileInterface
     */
    public function getProfile(int $reseller_id) : SkySwitchProfileInterface;

    /**
     * Get Seting method
     *
     * @param int|string $account_id
     * @return SkySwitchSettingsInterface
     */
    public function getSettings($account_id) : SkySwitchSettingsInterface;
}
