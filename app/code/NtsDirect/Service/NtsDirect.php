<?php

namespace NtsDirect\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use SkySwitch\Contracts\CheckStockResponse;
use SkySwitch\Contracts\CreateOrderParams;
use SkySwitch\Contracts\CreateOrderResponse;
use SkySwitch\Contracts\DistributorInterface;
use SkySwitch\Contracts\GetOrderResponse;
use SkySwitch\Contracts\GetShippingRatesParams;
use SkySwitch\Contracts\GetShippingRatesResponse;
use SkySwitch\Contracts\Traits\LogRequest;

class NtsDirect implements DistributorInterface
{
    use LogRequest;

    const FEDEX = 'fedex';
    const UPS = 'ups';
    const USPS = 'usps';
    const DEFAULT_START_PARAM = '1262322000';

    private $customer_key;

    private $api_key;

    private $username;

    private $base_url;

    private $client;

    public function __construct(array $credentials = []) {
        $this->api_key = $credentials['api_key'] ?? '';
        $this->customer_key = $credentials['customer_key'] ?? '';
        $this->base_url = $credentials['base_url'] ?? '';
        $this->username = $credentials['username'] ?? '';

        $this->client = new Client([
            'base_uri' => $this->base_url
        ]);
    }

    public function getServiceName(): string
    {
        return 'NTSDirect';
    }

    public function getProduct($sku = null)
    {
        $query = is_null($sku) ? '' : '?PartNumber=' . $sku;
        return $this->doRequest('Inventory/Item/list' . $query);
    }

    public function checkStock($sku): CheckStockResponse
    {
        $result = $this->getProduct($sku);

        return isset($result['ItemRequestResponse']['Items']['Quantity']) ? new CheckStockResponse($result['ItemRequestResponse']['Items']['Quantity'], $result['ItemRequestResponse']['Items']['Price']) : new CheckStockResponse(null, null, $result['status'] ?? 200, $result['error'] ?? '');
    }

    public function getOrders(array $params = [])
    {
        return $this->doRequest('Orders/Order/list' . $this->buildQueryParams($params));
    }

    public function getOrderDetails(array $params = []): GetOrderResponse
    {
        $order_id = $params['order_id'] ?? '';

        if (empty($order_id)) {
            return new GetOrderResponse();
        }

        $orders = $this->getOrders(['start' => self::DEFAULT_START_PARAM]);
        $filtered_orders = array_values(array_filter($orders['OrderRequestResponse'], function($order) use($order_id) {
            return $order['Order']['OrderID'] == $order_id;
        }));

        if (count($filtered_orders) !== 1) {
            return new GetOrderResponse();
        }

        $response = new GetOrderResponse();

        $response->setOrderStatus($filtered_orders[0]['Order']['OrderStatus']);
        
        return $response;
    }

    public function createOrder(CreateOrderParams $params): CreateOrderResponse
    {
        $args = [
            'Order' => [
                'Shipvia' => $params->getShippingMethod()
            ]
        ];

        $args['Order']['FedexQuoteId'] = $params->getQuoteNumber();

        $result = $this->doRequest('Orders/Order/create/', $args, Request::HTTP_METHOD_POST);

        if (isset($result['error']) && !empty($result['error'])) {
            return new CreateOrderResponse('', false, $result, $result['error']);
        }
        
        return new CreateOrderResponse($result['OrderRequestResponse']['Order']['OrderID'], (bool)$result['Success']);
    }

    public function getShippingRates(GetShippingRatesParams $params): GetShippingRatesResponse
    {
        $args = [
            "Contact" => [
                "PersonName" => $params->getContact()['name'],
                "CompanyName" => $params->getContact()['name'],
                "PhoneNumber" => $params->getContact()['phone'],
                "EmailAddress" => $params->getContact()['email']
            ],
            "Address" => [
                "StreetLines" => [
                    $params->getShippingAddress()['address1']
                ],
                "City" => $params->getShippingAddress()['city'],
                "StateOrProvinceCode" => $params->getShippingAddress()['state'],
                "PostalCode" => $params->getShippingAddress()['zip'],
                "CountryCode" => $params->getShippingAddress()['country'],
                "Residential" => "Y"
            ]
        ];

        foreach ($params->getItems() as $item) {
            $args['Parts'][] = [
                'PartNumber' => $item['sku'],
                'Quantity' => $item['qty']
            ];
        }

        $rates = $this->doRequest('Shipping/Shipment/list/', $args, Request::HTTP_METHOD_POST);

        if (isset($rates['error']) && !empty($rates['error'])) {
            return new GetShippingRatesResponse($rates['status'], $rates['error']);
        }
       
        $response = new GetShippingRatesResponse();

        $carrier = $rates[count($rates) - 1]['Carrier'] ?? '';
        $quote_id = $rates[count($rates) - 1]['QuoteId'] ?? '';

        foreach ($rates as $rate) {
            if (isset($rate['ServiceType'])) {
                $service_label = ucwords(strtolower(str_replace('_', ' ', $rate['ServiceType'])));
                $response->buildRate($service_label, $rate['Amount'], $carrier, $quote_id);
            }
        }

        return $response;
    }

    public function getCarrier($service_label): string
    {
        return $service_label;
    }

    public function getMethod($service_label): string
    {
        return strtoupper(str_replace(' ', '_', $service_label));
    }

    protected function buildQueryParams(array $params)
    {
        $query_string = '';
        $loop = 0;

        foreach ($params as $index => $param) {
            $query_string = $loop == 0 ? '?' . $index . '=' . $param : $query_string . '&' . $index . '=' . $param;
            $loop++;
        }

        return $query_string;
    }

    /**
     * Do API request with provided params
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     *
     * @return array
     */
    public function doRequest(
        string $endpoint,
        array $params = [],
        string $method = Request::HTTP_METHOD_GET,
        string $xml = ''
    ) {
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->customer_key . ':' . $this->api_key),
            'Username' => $this->username,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Timestamp' => time()
        ];

        try {
            $response = $this->client->request(
                $method,
                $endpoint,
                ['headers' => $headers, 'body' => json_encode($params)]
            );
        } catch (GuzzleException $exception) {
            $response = [
                'status' => $exception->getCode(),
                'error' => $exception->getMessage()
            ];
            $this->log($endpoint, $method, $params, $response);
            return $response;
        }

        $response_contents = json_decode($response->getBody()->getContents(), true);
        $this->log($endpoint, $method, $params, $response_contents);

        return $response_contents;
    }
}

