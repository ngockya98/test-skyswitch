<?php

namespace Voip888\Service;

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
/**
 * Class GitApiService
 */
class Voip888 implements DistributorInterface
{
    use LogRequest;

    private $username;

    private $password;

    private $base_url;

    private $client;

    private $token = null;

    public function __construct(array $credentials = []) {
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';
        $this->base_url = $credentials['base_url'] ?? '';

        $this->client = new Client([
            'base_uri' => $this->base_url
        ]);

        $this->getToken();
    }

    public function getServiceName(): string
    {
        return '888Voip';
    }

    public function getProduct($sku = null)
    {
        return $this->doRequest('products/' . $sku ?? '');
    }

    public function getShippingRates(GetShippingRatesParams $params): GetShippingRatesResponse
    {
        $args = [
            'quotes' => [[
                'shipping' => [
                    'address' => $params->getShippingAddress()['address1'],
                    'city' => $params->getShippingAddress()['city'],
                    'state' => $params->getShippingAddress()['state'],
                    'postcode' => $params->getShippingAddress()['zip'],
                    'countryCode' => $params->getShippingAddress()['country'],
                ]
            ]]
        ];

        if (!empty($params->getShippingAddress()['address2'])) {
            $args['quotes'][0]['shipping']['address2'] = $params->getShippingAddress()['address2'];
        }

        foreach ($params->getItems() as $item) {
            $args['quotes'][0]['items'][] = [
                'sku' => $item['sku'],
                'qty' => $item['qty']
            ];
        }

        $rates = $this->doRequest('quotes', $args, Request::HTTP_METHOD_POST);

        if (isset($rates['error']) && !empty($rates['error'])) {
            return new GetShippingRatesResponse($rates['status'], $rates['error']);
        }

        $response = new GetShippingRatesResponse();

        foreach ($rates['quotes'][0]['shipping_estimates'] as $service_label => $price) {
            $response->buildRate($service_label, $price);
        }

        return $response;
    }

    public function getOrders(array $params = [])
    {
        return $this->doRequest('orders/' . $params['order_id'] ?? '');
    }

    public function getOrderDetails(array $params = []): GetOrderResponse
    {
        $order = $this->getOrders($params);
        $response = new GetOrderResponse();

        if (!isset($order['order']['orderStatus'])) {
            return $response;
        }

        $response->setOrderStatus($order['order']['orderStatus']);

        foreach ($order['order']['items'] as $item) {
            if (isset($item['serialsAndMacs'])) {
                foreach ($item['serialsAndMacs'] as $mac) {
                    $response->buildMacs($item['sku'], $mac['serial'], $mac['mac']);
                }
            }
        }

        if (isset($order['order']['tracking'])) {
            foreach ($order['order']['tracking'] as $tracking) {
                $response->buildTrackingInfo($tracking['provider'], $tracking['trackingNumber']);
            }
        }
        
        return $response;
    }

    public function createOrder(CreateOrderParams $params): CreateOrderResponse
    {
        $args = [
            'orders' => [[
                'shipping' => [
                    'company' => $params->getContact()['company'] ?? '',
                    'firstName' => $params->getContact()['first_name'] ?? '',
                    'lastName' => $params->getContact()['last_name'] ?? '',
                    'address' => $params->getShippingAddress()['address1'],
                    'city' => $params->getShippingAddress()['city'],
                    'state' => $params->getShippingAddress()['state'],
                    'postcode' => $params->getShippingAddress()['zip'],
                    'countryCode' => $params->getShippingAddress()['country'],
                ],
                'billing' => [
                    'company' => $params->getContact()['company'] ?? '',
                    'firstName' => $params->getContact()['first_name'] ?? '',
                    'lastName' => $params->getContact()['last_name'] ?? '',
                    'address' => $params->getBillingAddress()['address1'],
                    'city' => $params->getBillingAddress()['city'],
                    'state' => $params->getBillingAddress()['state'],
                    'postcode' => $params->getBillingAddress()['zip'],
                    'countryCode' => $params->getBillingAddress()['country'],
                ],
                'poNumber' => $params->getPoNumber(),
                'shippingMethod' => $params->getShippingMethod(),
            ]]
        ];

        if (!empty($params->getShippingAddress()['address2'])) {
            $args['orders'][0]['shipping']['address2'] = $params->getShippingAddress()['address2'];
        }

        if (!empty($params->getBillingAddress()['address2'])) {
            $args['orders'][0]['billing']['address2'] = $params->getBillingAddress()['address2'];
        }

        foreach ($params->getItems() as $item) {
            $args['orders'][0]['items'][] = [
                'sku' => $item['sku'],
                'qty' => $item['qty']
            ];
        }

        $result = $this->doRequest('orders', $args, Request::HTTP_METHOD_POST);

        if (isset($result['error']) && !empty($result['error'])) {
            return new CreateOrderResponse('', false, $result, $result['error']);
        }

        return new CreateOrderResponse($result['orderNumbers'][0], $result['response'] === 'success');
    }

    public function checkStock($sku): CheckStockResponse
    {
        $result = $this->getProduct($sku);
        return isset($result['product']) ? new CheckStockResponse($result['product']['qty'], $result['product']['price']) : new CheckStockResponse(null, null, $result['status'] ?? 200, $result['error'] ?? '');
    }

    public function getToken()
    {
        if (!is_null($this->token)) {
            return $this->token;
        }
        
        $response = $this->doRequest('create-token', ['email' => $this->username, 'password' => $this->password],
        Request::HTTP_METHOD_POST);
     
        $this->token = $response['token'] ?? null;
        return $this->token;
    }

    public function __destruct()
    {
        $this->revokeToken();
    }

    public function getCarrier($service_label): string
    {
        return $service_label;
    }

    public function getMethod($service_label): string
    {
        return $service_label;
    }

    protected function revokeToken()
    {
        $this->doRequest('revoke-token', [],Request::HTTP_METHOD_DELETE);
        $this->token = null;
    }

    protected function buildQueryParams(array $params)
    {
        if (empty($params)) {
            return '';
        }

        $query_string = '';

        foreach ($params as $index => $param) {
            if ($index === 0) {
                $query_string = '?filter[' . ($index + 1) . '][attribute]=' . $param['attribute'] . '&filter[' . ($index + 1) . '][like]=' . $param['value'];
                continue;
            }

            $query_string = '&filter[' . ($index + 1) . '][attribute]=' . $param['attribute'] . '&filter[' . ($index + 1) . '][like]=' . $param['value'];
        }

        return $query_string;
    }

    protected function buildShippingRatesXml(array $data)
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?><api_request><data_item>";

        foreach ($data as $key => $value) {
            if ($key === 'items') {
                $xml .= '<items>';
                foreach ($value as $items) {
                    $xml .= '<item>';
                    foreach ($items as $attr => $item) {
                        $xml .= '<' . $attr . '>' . $item . '</' . $attr . '>';
                    }
                    $xml .= '</item>';
                }
                $xml .= '</items>';
                continue;
            }
            $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $xml .= "</data_item></api_request>";

        return $xml;
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
            'Accept' => 'application/json'
        ];

        if ($endpoint !== 'create-token') {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        $options = [
            'headers' => $headers, 'form_params' => $params
        ];

        try {
            $response = $this->client->request(
                $method,
                $endpoint,
                $options
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

