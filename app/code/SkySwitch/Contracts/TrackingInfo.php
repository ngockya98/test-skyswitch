<?php

namespace SkySwitch\Contracts;

class TrackingInfo
{
    /**
     * @var string
     */
    protected $provider;

    /**
     * @var string
     */
    protected $tracking_number;

    /**
     * @param string $provider
     * @param string $tracking_number
     */
    public function __construct(string $provider, string $tracking_number)
    {
        $this->provider = $provider;
        $this->tracking_number = $tracking_number;
    }

    /**
     * Return Provider
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Return Tracking Number
     *
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->tracking_number;
    }
}
