<?php

namespace SkySwitch\Contracts;

class GetShippingRatesResponse
{
    /**
     * @var array
     */
    protected array $rates = [];

    /**
     * @var string
     */
    protected String $status;

    /**
     * @var string
     */
    protected string $error;

    /**
     * @param string $status
     * @param string $error
     */
    public function __construct(string $status = '200', string $error = '')
    {
        $this->status = $status;
        $this->error = $error;
    }

    /**
     * Set rate value
     *
     * @param string $service_label
     * @param float $price
     * @param string $carrier
     * @param string $quote_id
     * @return void
     */
    public function buildRate(string $service_label, float $price, string $carrier = '', string $quote_id = '')
    {
        $this->rates[] = [
            'service_label' => $service_label,
            'price' => $price,
            'carrier' => $carrier,
            'quote_id' => $quote_id
        ];
    }

    /**
     * Return rates array
     *
     * @return array
     */
    public function getRates(): array
    {
        return $this->rates;
    }
}
