<?php

namespace SkySwitch\Contracts;

class Mac
{
    /**
     * @var string
     */
    protected $sku;

    /**
     * @var string
     */
    protected $serial;

    /**
     * @var string
     */
    protected $mac;

    /**
     * @param string $sku
     * @param string $serial
     * @param string $mac
     */
    public function __construct(string $sku, string $serial = '', string $mac = '')
    {
        $this->sku = $sku;
        $this->serial = $serial;
        $this->mac = $mac;
    }

    /**
     * Return Sku
     *
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Return Serial
     *
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial;
    }

    /**
     * Return mac
     *
     * @return string
     */
    public function getMac(): string
    {
        return $this->mac;
    }
}
