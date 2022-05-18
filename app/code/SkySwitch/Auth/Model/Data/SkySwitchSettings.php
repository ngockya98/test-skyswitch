<?php

namespace SkySwitch\Auth\Model\Data;

use SkySwitch\Auth\Api\Data\SkySwitchSettingsInterface;

class SkySwitchSettings implements SkySwitchSettingsInterface
{
    /**
     * @var array|mixed
     */
    protected array $settings_response;

    /**
     * @param array|mixed $settings_response
     */
    public function __construct($settings_response = [])
    {
        $this->settings_response = $settings_response;
    }

    /**
     * Return Setting response array
     *
     * @return array|mixed
     */
    public function getSettings()
    {
        return $this->settings_response;
    }

    /**
     * Return Send Grid Credentials
     *
     * @return void
     */
    public function getSendGridCredentials() // phpcs:ignore
    {
        //@todo: we need to move these settings from Wordpress to SkySwitch API
    }

    /**
     * Return Yealink Credentials
     *
     * @return void
     */
    public function getYealinkCreddentials() // phpcs:ignore
    {
        //@todo: @see getSendGridCredentials
    }

    /**
     * Return Yealink RPS Credentials
     *
     * @return void
     */
    public function getYealinkRPSCredentials() // phpcs:ignore
    {
        // TODO: @see getSendGridCredentials
    }

    /**
     * Return Jenne Credentials
     *
     * @return void
     */
    public function getJenneCredentials() // phpcs:ignore
    {
        // TODO: @see getSendGridCredentials
    }

    /**
     * Return 888 Voip Credentials
     *
     * @return void
     */
    public function get888VoipCredentials() // phpcs:ignore
    {
        // TODO: @see getSendGridCredentials
    }

    /**
     * Return PolyZTP Credentials
     *
     * @return void
     */
    public function getPolyZTPCredentials() // phpcs:ignore
    {
        // TODO: @see getSendGridCredentials
    }

    /**
     * Return 3cx Credentials
     *
     * @return void
     */
    public function get3cxCredentials() // phpcs:ignore
    {
        // TODO: @see getSendGridCredentials
    }
}
