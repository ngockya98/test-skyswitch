<?php

namespace Shipwire\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Rest\Request;
use SkySwitch\Contracts\CheckStockResponse;
use SkySwitch\Contracts\CreateOrderParams;
use SkySwitch\Contracts\CreateOrderResponse;
use SkySwitch\Contracts\DistributorInterface;
use SkySwitch\Contracts\GetOrderResponse;
use SkySwitch\Contracts\GetShippingRatesParams;
use SkySwitch\Contracts\GetShippingRatesResponse;
use SkySwitch\Contracts\Traits\LogRequest;

class Shipwire implements DistributorInterface
{
    use LogRequest;

    const GET_SHIPPING_RATES_REQUIRED =  [
        'address1',
        'city',
        'postalCode',
        'region',
        'country',
        'items'
    ];

    private $username;

    private $password;

    private $base_url;

    private $ship_from;

    public function __construct(array $credentials = []) {
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';
        $this->base_url = $credentials['base_url'] ?? '';
        $this->ship_from = $credentials['ship_from'] ?? '';

        $this->client = new Client([
            'base_uri' => $this->base_url
        ]);
    }

    public function getServiceName(): string
    {
        return 'Shipwire';
    }

    public function checkStocks($offset = 0, $limit = 100)
    {   
        return $this->doRequest('v3/stock?limit=' . $limit . '&offset=' . $offset);
    }

    public function checkStock($sku): CheckStockResponse
    {
        if (empty($sku)) {
            return new CheckStockResponse(0, 0);
        }
        $result = $this->doRequest('v3/stock?sku=' . $sku);
        
        if ((isset($result['error']) && !empty($result['error']) || empty($result['resource']['items']) )) {
            return new CheckStockResponse(0, 0, $result['status'] ?? '', $result['error'] ?? '');
        }
        $response = new CheckStockResponse($result['resource']['items'][0]['resource']['good'] ?? 0);
        $product_result = $this->doRequest('v3/products/' . $result['resource']['items'][0]['resource']['productId'] ?? '');
    
        $response->setPrice($product_result['resource']['values']['resource']['retailValue'] ?? 0);
        return $response;
    }

    public function getShippingRates(GetShippingRatesParams $params): GetShippingRatesResponse
    {

        $args = [
            'order' => [
                'address1' => $params->getShippingAddress()['address1'],
                'address2' => $params->getShippingAddress()['address2'],
                'address3' => '',
                'city' => $params->getShippingAddress()['city'],
                'postalCode' => $params->getShippingAddress()['zip'],
                'region' => $params->getShippingAddress()['state'],
                'country' => $params->getShippingAddress()['country'],
                'isCommercial' => 1,
                'isPoBox' => 0
            ]
        ];

        foreach ($params->getItems() as $item) {
            $args['items'][] = [
                'sku' => $item['sku'],
                'quantity' => $item['qty']
            ];
        }

        $data = [
            'order' => [
                'shipTo' => $args['order'],
                'items' => $args['items']
            ]
        ];

        if (isset($args['options'])) {
            $data['options'] = $args['options'];
        }

        $rates = $this->doRequest('v3/rate', $data, Request::HTTP_METHOD_POST);
    
        if (isset($rates['error']) && !empty($rates['error'])) {
            return new GetShippingRatesResponse($rates['status'], $rates['error']);
        }
        $response = new GetShippingRatesResponse();

        foreach ($rates['resource']['rates'][0]['serviceOptions'] as $rate) {
            $response->buildRate($rate['shipments'][0]['carrier']['code'] . ' ' . $rate['serviceLevelCode'], $rate['shipments'][0]['cost']['amount']);
        }

        return $response;
    }

    public function getOrderDetails(array $params = []): GetOrderResponse
    {
        $order = $this->getOrder($params['order_id'] ?? '');
        $response = new GetOrderResponse();
        
        if (!isset($order['resource']['status'])) {
            return $response;
        }
        $response->setOrderStatus($order['resource']['status']);
        $tracking_info = $this->getTrackingInfo($order['resource']['id'] ?? '');

        if (isset($tracking_info['resource']['items'])) {
            foreach ($tracking_info['resource']['items'] as $tracking) {
                $response->buildTrackingInfo($tracking['resource']['carrierCode'], $tracking['resource']['tracking']);
            }
        }

        $items = $this->getOrderItems($order['resource']['id'] ?? '');
        
        if (isset($items['resource']['items'])) {
            foreach ($items['resource']['items'] as $item) {
                if (isset($item['serialNumbers']['resource']['items'])) {
                    foreach ($item['serialNumbers']['resource']['items'] as $serial) {
                        $response->buildMacs($item['resource']['sku'], $serial['resource']['serialNumber']);
                    }
                }
            }
        }
        
        return $response;
    }

    public function getOrders(array $params = [])
    {
        $offset = $params['offset'] ?? 0;
        $limit = $params['limit'] ?? 100;

        return $this->doRequest('v3/orders?limit=' . $limit . '&offset=' . $offset);
    }

    public function getOrder($external_id = null, $order_id = null)
    {
        if (is_null($order_id) && is_null($external_id)) {
            return [
                'status' => 422,
                'error' => 'Order_id or external_id must be provided'
            ];
        }

        $endpoint = !is_null($order_id) ? "v3/orders/{$order_id}" : "v3/orders/E{$external_id}";
        return $this->doRequest($endpoint);
    }

    public function getOrderItems($order_id = null, $external_id = null)
    {
        if (is_null($order_id) && is_null($external_id)) {
            return [
                'status' => 422,
                'error' => 'Order_id or external_id must be provided'
            ];
        }

        $endpoint = !is_null($order_id) ? "v3/orders/{$order_id}/items" : "v3/orders/E{$external_id}/items";
        
        return $this->doRequest($endpoint);
    }

    public function getCarriers($offset = 0, $limit = 30)
    {
        return $this->doRequest('v3.1/carriers?limit=' . $limit . '&offset=' . $offset);
    }

    public function getTrackingInfo($order_id = null, $external_id = null)
    {
        if (is_null($order_id) && is_null($external_id)) {
            return [
                'status' => 422,
                'error' => 'Order_id or external_id must be provided'
            ];
        }

        $endpoint = !is_null($order_id) ? "v3/orders/{$order_id}/trackings" : "v3/orders/E{$external_id}/trackings";
        return $this->doRequest($endpoint);
    }

    public function getProduct($sku = null)
    {

    }

    public function createOrder(CreateOrderParams $params): CreateOrderResponse
    {
        $args = [
            "orderNo" =>  $params->getPoNumber(),
            "externalId" => $params->getPoNumber(),
            "options" => [
                "serviceLevelCode" => $params->getShippingMethod(),
                "carrierCode" => $params->getShippingCarrier(),
                "sameDay" => "NOT REQUESTED",
                "currency" => "USD",
                "server" => "Production"
            ],
            "shipFrom" => $this->ship_from,
            "shipTo" => [
                "email" => $params->getContact()['email'],
                "name" =>  $params->getContact()['first_name'] . ' ' . $params->getContact()['last_name'],
                "company" => $params->getContact()['company'],
                "address1" => $params->getShippingAddress()['address1'],
                "address2" => $params->getShippingAddress()['address2'] ?? null,
                "city" => $params->getShippingAddress()['city'],
                "state" => $params->getShippingAddress()['state'],
                "postalCode" => $params->getShippingAddress()['zip'],
                "country" => $params->getShippingAddress()['country'],
                "phone" => $params->getShippingAddress()['phone'],
                "isCommercial" => (int) ($params->getShippingAddress()['address_type'] === CreateOrderParams::COMERCIAL),
                "isPoBox" => 0
            ]
        ];

        foreach ($params->getItems() as $item) {
            $args['items'][] = [
                'sku' => $item['sku'],
                'quantity' => $item['qty']
            ];
        }

        $result = $this->doRequest('v3/orders', $args, Request::HTTP_METHOD_POST);

        if (isset($result['error']) && !empty($result['error'])) {
            return new CreateOrderResponse('', false, $result, $result['error']);
        }

        return new CreateOrderResponse($result['resource']['items'][0]['resource']['orderNo'], $result['status'] == 200);
    }

    public function getCarrier($service_label): string
    {
        $pieces = explode(' ', $service_label);
        unset($pieces[count($pieces) - 1]);
        return implode(' ', $pieces);
    }

    public function getMethod($service_label): string
    {
        $pieces = explode(' ', $service_label);
        return $pieces[count($pieces) - 1];
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
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password)
        ];

        $options = $method === Request::HTTP_METHOD_GET ? ['headers' => $headers] : ['headers' => $headers, 'body' => json_encode($params)];

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

    protected function validateRequiredParams($required_keys, $params)
    {
        $missing_required_keys = array_diff($required_keys, array_keys($params));
        if (!empty($missing_required_keys)) {
            throw new InvalidArgumentException(new Phrase('Missing required arguments: ' . implode(',', $missing_required_keys)), 422);
        }
    }
}

