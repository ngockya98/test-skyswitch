<?php

namespace SkySwitch\Contracts;

class CreateOrderResponse
{
    /**
     * @var string
     */
    protected string $order_id;

    /**
     * @var string|bool
     */
    protected string $success;

    /**
     * @var string
     */
    protected string $error;

    /**
     * @var array
     */
    protected $raw_response;

    /**
     * @param string $order_id
     * @param bool $success
     * @param array $raw_response
     * @param string $error
     */
    public function __construct(string $order_id, bool $success, array $raw_response = [], string $error = '')
    {
        $this->order_id = $order_id;
        $this->success = $success;
        $this->error = $error;
        $this->raw_response = $raw_response;
    }

    /**
     * Return Order id
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->order_id;
    }

    /**
     * Check order response is successful
     *
     * Return boolean value
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Return error response
     *
     * @return bool
     */
    public function getError(): bool
    {
        return $this->error;
    }

    /**
     * Return response raw
     *
     * @return array
     */
    public function getRawResponse(): array
    {
        return $this->raw_response;
    }
}
