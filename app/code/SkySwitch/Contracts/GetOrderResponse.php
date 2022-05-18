<?php

namespace SkySwitch\Contracts;

class GetOrderResponse
{
    /**
     * @var array
     */
    protected array $trackings = [];

    /**
     * @var string
     */
    protected String $order_status = '';

    /**
     * @var array
     */
    protected array $macs = [];

    /**
     * Set macs value
     *
     * @param string $sku
     * @param string $serial
     * @param string $mac
     * @return void
     */
    public function buildMacs(string $sku, string $serial = '', string $mac = '')
    {
        $this->macs[] = new Mac($sku, $serial, $mac);
    }

    /**
     * Set tracking info
     *
     * @param string $provider
     * @param string $tracking_number
     * @return void
     */
    public function buildTrackingInfo(string $provider = '', string $tracking_number = '')
    {
        $this->trackings[] = new TrackingInfo($provider, $tracking_number);
    }

    /**
     * Set order status
     *
     * @param mixed $order_status
     * @return void
     */
    public function setOrderStatus($order_status)
    {
        $this->order_status = $order_status;
    }

    /**
     * Return macs values
     *
     * @return array
     */
    public function getMacs(): array
    {
        return $this->macs;
    }

    /**
     * Return trackings info
     *
     * @return array
     */
    public function getTrackings(): array
    {
        return $this->trackings;
    }

    /**
     * Return order status
     *
     * @return string
     */
    public function getOrderStatus(): string
    {
        return ucwords($this->order_status);
    }
}
