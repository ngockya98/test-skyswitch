<?php

namespace SkySwitch\Contracts;

class CheckStockResponse
{
    /**
     * @var mixed|null
     */
    protected $stock;

    /**
     * @var mixed|null
     */
    protected $price;

    /**
     * @var int|mixed
     */
    protected $status;

    /**
     * @var mixed|string
     */
    protected $error;

    /**
     * @param mixed $stock
     * @param mixed $price
     * @param mixed $status
     * @param string $error
     */
    public function __construct($stock = null, $price = null, $status = 200, $error = '')
    {
        $this->stock = $stock;
        $this->price = $price;
        $this->status = $status;
        $this->error = $error;
    }

    /**
     * Return Stock
     *
     * @return mixed|null
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Return Price
     *
     * @return mixed|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Return Status
     *
     * @return int|mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Return error
     *
     * @return mixed|string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set price value
     *
     * @param mixed $value
     * @return void
     */
    public function setPrice($value)
    {
        $this->price = $value;
    }
}
