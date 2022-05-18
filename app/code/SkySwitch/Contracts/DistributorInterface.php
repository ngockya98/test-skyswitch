<?php

namespace SkySwitch\Contracts;

use Magento\Framework\Webapi\Rest\Request;

/**
 * Interface for integratin API services.
 */
interface DistributorInterface
{
    /**
     * Get service name method
     *
     * @return string
     */
    public function getServiceName(): string;

    /**
     * Log method
     *
     * @param string $endpoint
     * @param string $method
     * @param array $request
     * @param array $response
     * @return mixed
     */
    public function log(string $endpoint, string $method, array $request, array $response);

    /**
     * Get product method
     *
     * @param null|mixed $sku
     * @return mixed
     */
    public function getProduct($sku = null);

    /**
     * Check stock method
     *
     * @param mixed $sku
     * @return CheckStockResponse
     */
    public function checkStock($sku): CheckStockResponse;

    /**
     * Get shipping rate method
     *
     * @param GetShippingRatesParams $params
     * @return GetShippingRatesResponse
     */
    public function getShippingRates(GetShippingRatesParams $params): GetShippingRatesResponse;

    /**
     * Get order detail method
     *
     * @param array $params
     * @return GetOrderResponse
     */
    public function getOrderDetails(array $params = []): GetOrderResponse;

    /**
     * Create order method
     *
     * @param CreateOrderParams $params
     * @return CreateOrderResponse
     */
    public function createOrder(CreateOrderParams $params): CreateOrderResponse;

    /**
     * Get Carrier method
     *
     * @param mixed $service_label
     * @return string
     */
    public function getCarrier($service_label): string;

    /**
     * Get method
     *
     * @param mixed $service_label
     * @return string
     */
    public function getMethod($service_label): string;

    /**
     * Process request method
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     * @param string $xml
     * @return mixed
     */
    public function doRequest(
        string $endpoint,
        array $params = [],
        string $method = Request::HTTP_METHOD_GET,
        string $xml = ''
    );
}
