<?php

namespace Teledynamics\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use phpDocumentor\Reflection\PseudoTypes\False_;
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
class Teledynamics implements DistributorInterface
{
    use LogRequest;

    const SS_TEST_CUSTOMER = 'SKY8700';

    const SERVICE_LABEL_CARRIER_MAP = [
        'Fed' => 'FedEx',
        'Ups' => 'UPS',
        'Pos' => 'USPS'
    ];
    /**
     * @var ResponseFactory
     */
    private $response_factory;

    /**
     * @var ClientFactory
     */
    private $client_factory;

    private $username;

    private $password;

    private $base_url;

    private $client;

    private $test;

    public function __construct(array $credentials = []) {
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';
        $this->base_url = $credentials['base_url'] ?? '';
        $this->address_key = $credentials['address_key'] ?? '';
        $this->test = $credentials['test'] ?? false;

        $this->client = new Client([
            'base_uri' => $this->base_url
        ]);
    }

    public function getServiceName(): string
    {
        return 'Teledynamics';
    }

    public function checkStock($sku): CheckStockResponse
    {
        $result = $this->doRequest('product/'. $sku . '/CheckQuantity');

        return isset($result['Quantity']) ? new CheckStockResponse($result['Quantity'], $result['UnitPrice']) : new CheckStockResponse(null, null, $result['status'], $result['error']);
    }

    public function getProduct($sku = null)
    {
        return $this->doRequest('product/'. $sku);
    }

    public function checkManufacturerStock($manufacturer)
    {
        return $this->doRequest('product/manufacturer/'. $manufacturer . '/CheckQuantity');
    }

    public function getManufacturerProducts($manufacturer)
    {
        return $this->doRequest('product/manufacturer/'. $manufacturer);
    }

    public function getOrder($po_number)
    {
        $order = $this->doRequest('orders/'. $po_number);
        $order['items'] = [];
        $order['trackings'] = [];

        foreach ($order["TrackingInformation"] as $track) {
            array_push($order["trackings"], array(
                "carrier" => $track["Carrier"],
                "tracking" => $track["TrackingNumber"],
                "status" => $track["Status"]
            ));
        }

        foreach ($order["OrderLines"] as $item) {
            $macs = array();
            foreach ($item["SerializationInformation"] as $device) {
                array_push($macs, array(
                    "mac" => $device["MAC"],
                    "serial" => $device["SerialNumber"]
                ));
            }
            array_push($order["items"], array(
                "name" => $item["ProductName"],
                "sku" => $item["PartNumber"],
                "quantity" => $item["Quantity"],
                "macs" => $macs
            ));
        }

        return $order;
    }

    public function getOrderDetails(array $params = []): GetOrderResponse
    {
        if (!isset($params['order_id']) || empty($params['order_id'])) {
            return new GetOrderResponse();
        }
        $order = $this->doRequest('orders/'. $params['order_id'] ?? '');

        $response = new GetOrderResponse();

        if (!isset($order['Status'])) {
            return $response;
        }

        $response->setOrderStatus($order['Status']);

        foreach ($order["TrackingInformation"] as $tracking) {
            $response->buildTrackingInfo($tracking['Carrier'], $tracking['TrackingNumber']);
        }

        foreach ($order["OrderLines"] as $item) {
            if ($item['PartNumber'] !== 'PROVISIONING') {
                foreach ($item["SerializationInformation"] as $device) {
                    $response->buildMacs($item['PartNumber'], $device['SerialNumber'], $device['MAC']);
                }
            }
        }
        
        return $response;
    }

    public function createOrder(CreateOrderParams $params): CreateOrderResponse
    {
        $args = [
            'PONumber' => $this->test ? self::SS_TEST_CUSTOMER . '-' . date('His') : $params->getPoNumber(),
            'IsProvisioningOrder' => false,
            'ShippingAddress' => [
                'Label' =>  $params->getShippingAddress()['address_type'],
                'Address1' => $params->getShippingAddress()['address1'],
                'City' => $params->getShippingAddress()['city'],
                'StateOrProvince' => $params->getShippingAddress()['state'],
                'State' => $params->getShippingAddress()['state'],
                'ZipCode' => $params->getShippingAddress()['zip'],
                'Country' => $params->getShippingAddress()['country']
            ]
        ];

        foreach ($params->getItems() as $item) {
            $args['OrderLines'][] = [
                'PartNumber' => $item['sku'],
                'Quantity' => $item['qty'],
                'ShouldProvision' => false
            ];
        }

        $args['Shipping'] = [
            'Carrier' => $params->getShippingCarrier(),
            'ShippingMethod' => $params->getShippingMethod(),
            'Quote' => 0
        ];
        $args['ShipmentTypeAddressKey'] = $this->address_key;

        $result = $this->doRequest('orders', $args, Request::HTTP_METHOD_POST);

        if (isset($result['error']) || !isset($result['PONumber'])) {
            return new CreateOrderResponse('', false, $result, $result['error'] ?? '');
        }
 
        return new CreateOrderResponse($result['OrderNumber'] ?? $result['PONumber'], true, $result);
    }

    public function getShippingRates(GetShippingRatesParams $params): GetShippingRatesResponse
    {
        $args = [
            'PONumber' => $params->getPoNumber(),
            'IsProvisioningOrder' => $params->getProvision(),
            'ShippingAddress' => [
                'Label' =>  $params->getShippingAddress()['address_type'],
                'Address1' => $params->getShippingAddress()['address1'],
                'City' => $params->getShippingAddress()['city'],
                'StateOrProvince' => $params->getShippingAddress()['state'],
                'State' => $params->getShippingAddress()['state'],
                'ZipCode' => $params->getShippingAddress()['zip'],
                'Country' => $params->getShippingAddress()['country']
            ]
        ];

        foreach ($params->getItems() as $item) {
            $args['OrderLines'][] = [
                'PartNumber' => $item['sku'],
                'Quantity' => $item['qty'],
                'ShouldProvision' => $params->getProvision()
            ];
        }

        $args['Shipping'] = [
            'Carrier' => 'Fedex',
            'ShippingMethod' => 'FED_2DY',
            'Quote' => 0
        ];
        $args['ShipmentTypeAddressKey'] = $this->address_key;

        $rates = $this->doRequest('quotes', $args, Request::HTTP_METHOD_POST);

        if (isset($rates['error']) && !empty($rates['error'])) {
            return new GetShippingRatesResponse($rates['status'], $rates['error']);
        }

        $response = new GetShippingRatesResponse();

        foreach ($rates['ShippingOptions'] as $rate) {
            $service_label = ucwords(strtolower(str_replace('_', ' ', $rate['ShippingMethod'])));
            $response->buildRate($service_label, $rate['Quote']);
        }

        return $response;
    }

    public function getCarrier($service_label): string
    {
        return self::SERVICE_LABEL_CARRIER_MAP[explode(' ', $service_label)[0]];
    }

    public function getMethod($service_label): string
    {
        return strtoupper(str_replace(' ', '_', $service_label));
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
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
            'Content-Type' => 'application/json'
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

