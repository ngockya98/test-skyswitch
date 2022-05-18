<?php

namespace Jenne\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Jenne\Service\Exception\InvalidArgumentException;
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

class Jenne implements DistributorInterface
{
    use LogRequest;

    private $username;

    private $password;

    private $base_url;

    private $soap_url;

    private $client;

    const CREATE_ORDER_REQUIRED =  [
        'quote_number',
        'po_number',
        'company',
        'address',
        'city',
        'state',
        'country',
        'phone',
        'postal_code',
        'payment_method',
        'cc_id',
        'buyer',
        'end_user_email',
        'end_user_po_number',
        'customer_id',
        'ship_to_email'
    ];

    const SUPPORTED_SHIPPING_METHODS = [
        'F01', 
        'F11', 
        'F14', 
        'R02'
    ];

    public function __construct(array $credentials = []) {
        $this->username = $credentials['username'] ?? '';
        $this->password = $credentials['password'] ?? '';
        $this->base_url = $credentials['base_url'] ?? '';
        $this->soap_url = $credentials['soap_url'] ?? '';

        $this->client = new Client([
            'base_uri' => $this->base_url
        ]);
    }

    public function getServiceName(): string
    {
        return 'Jenne';
    }

    public function getProduct($sku = null)
    {
        $request_xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <FindProducts xmlns="http://WebService.jenne.com">
              <email>' . $this->username . '</email>
              <password>' . $this->password . '</password>
              <findString>' . ($sku ?? '') . '</findString>
            </FindProducts>
          </soap:Body>
        </soap:Envelope>';
         
        return $this->doRequest('FindProducts', [], Request::HTTP_METHOD_POST, $request_xml);
    }

    public function checkStock($sku): CheckStockResponse
    {
        $request_xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetPriceAvailability_v2 xmlns="http://WebService.jenne.com">
              <email>' . $this->username . '</email>
              <password>' . $this->password . '</password>
              <productList>' . $sku . '</productList>
            </GetPriceAvailability_v2>
          </soap:Body>
        </soap:Envelope>';
         
        $result = $this->doRequest('GetPriceAvailability_v2', [], Request::HTTP_METHOD_POST, $request_xml);

        return isset($result['PriceAvailabilities']['PriceAvailabilityV2']) ? new CheckStockResponse($result['PriceAvailabilities']['PriceAvailabilityV2']['AvailableQuantity'], $result['PriceAvailabilities']['PriceAvailabilityV2']['Price']) : new CheckStockResponse(null, null, $result['status'] ?? 200, $result['error'] ?? '');
    }

    public function getShippingRates(GetShippingRatesParams $params): GetShippingRatesResponse
    {
        $rates = [];

        foreach (self::SUPPORTED_SHIPPING_METHODS as $method) {
            try {
                $result = $this->getShippingRate($params, $method);
            } catch (InvalidArgumentException $e) {
                continue;
            }
            array_push($rates, $result);
        }

        $response = new GetShippingRatesResponse();

        foreach ($rates as $rate) {
            $response->buildRate($rate['label'], $rate['cost'], '', $rate['quote_number']);
        }

        return $response;
    }

    protected function getShippingRate(GetShippingRatesParams $params, string $carrier_code)
    {
        $request_xml = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <GetQuote_v3 xmlns="http://WebService.jenne.com">
                <email>' . $this->username . '</email>
                <password>' . $this->password . '</password>
                <specialBid></specialBid>
                <carrierCode>' . ($carrier_code) . '</carrierCode>
                <svcSaturday>N</svcSaturday>
                <svcInsurance>N</svcInsurance>
                <svcHoldAtLoc>N</svcHoldAtLoc>
                <svcInside>N</svcInside>
                <svcLiftgate>N</svcLiftgate>
                <svcPod>N</svcPod>
                <svcPodAdult>N</svcPodAdult>
                <shipToName>' . $params->getContact()['name'] . '</shipToName>
                <shipToAddress1>' . $params->getShippingAddress()['address1'] . '</shipToAddress1>
                <shipToAddress2>' . ($params->getShippingAddress()['address2'] ?? '') . '</shipToAddress2>
                <shipToCity>' . $params->getShippingAddress()['city'] . '</shipToCity>
                <shipToState>' . $params->getShippingAddress()['state'] . '</shipToState>
                <shipToCountry>' . $params->getShippingAddress()['country'] . '</shipToCountry>
                <shipToPhone>' . $params->getShippingAddress()['phone'] . '</shipToPhone>
                <shipToContact>' . $params->getContact()['name'] . '</shipToContact>
                <shipToPostalCode>' . $params->getShippingAddress()['zip'] . '</shipToPostalCode>
                <shipAddressType>' .($params->getShippingAddress()['address_type'] ?? 'default') . '</shipAddressType>
                <shipSpecialInstructions></shipSpecialInstructions>
                <freightType>1</freightType>
                <freightAccountNo></freightAccountNo>
                <vcpId></vcpId>
                <items>';

            foreach ($params->getItems() as $item) {
                $request_xml .= '<OrderItem>
                <PartNo>' . $item['sku'] . '</PartNo>
                <Quantity>' . $item['qty'] . '</Quantity>
                <unique_identifier>' . $item['sku'] . '</unique_identifier>
            </OrderItem>';
            }

            $request_xml .= '</items>
            </GetQuote_v3>
          </soap:Body>
        </soap:Envelope>';
                
        $result = $this->doRequest('GetQuote_v3', [], Request::HTTP_METHOD_POST, $request_xml);

        $shipping_cost = round(floatval($result["Freight"]), 2);
        $quote_number = $result["QuoteNumber"];

        switch ($result["CarrierCode"]) {
            case "F01":
                $shipping_label = "FedEx Priority Overnight";
                $shipping_carrier = "FedEx";
                break;
            case "F14":
                $shipping_label = "FedEx Express Saver";
                $shipping_carrier = "FedEx";
                break;
            case "F11":
                $shipping_label = "FedEx 2 Day";
                $shipping_carrier = "FedEx";
                break;
            case "R02":
                $shipping_label = "FedEx Ground";
                $shipping_carrier = "FedEx";
                break;
            default:
                $shipping_label = "";
                $shipping_carrier = "";
        }

        $response = array(
            "quote_number" => "",
            "cost" => "",
            "label" => $shipping_label,
            "carrier" => $shipping_carrier,
            "code" => $result["CarrierCode"],
        );
        if ($quote_number != "") {
            $response["quote_number"] = $quote_number;
            $response["cost"] = $shipping_cost;
        }

        return $response;
    }

    public function getOrders(array $params = [])
    {
        $request_xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
          <soap:Body>
            <GetInvoices_v2 xmlns="http://WebService.jenne.com">
              <email>' . $this->username . '</email>
              <password>' . $this->password . '</password>
              <poNumber>' .($params['order_id'] ?? '') . '</poNumber>
              <startDate>' .($params['start_date'] ?? '') . '</startDate>
              <endDate>' .($params['end_date'] ?? '') . '</endDate>
            </GetInvoices_v2>
          </soap:Body>
        </soap:Envelope>';
        
        $result = $this->doRequest('GetInvoices_v2', [], Request::HTTP_METHOD_POST, $request_xml);
        return $result;
    }

    public function getOrderDetails(array $params = []): GetOrderResponse
    {
        $order = $this->getOrders($params);

        $response = new GetOrderResponse();

        if (!isset($order['Invoices']['InvoiceV2'])) {
            return $response;
        }

        $response->setOrderStatus($order['Invoices']['InvoiceV2']['OrderStatus']);
        $trackings = explode(',', $order['Invoices']['InvoiceV2']['TrackingNumbers']);

        foreach ($trackings as $tracking) {
            if (empty($tracking)) {
                continue;
            }
            $response->buildTrackingInfo($order['Invoices']['InvoiceV2']['ShipVia'], $tracking);
        }

        foreach ($order['Invoices']['InvoiceV2']['InvoiceLines']['InvoiceLineV2'] as $item) {
            if (empty($item['SerialNumbers'])) {
                continue;
            }
            $serials = explode(',', $item['SerialNumbers']);
            foreach ($serials as $serial) {
                if (empty($serial)) {
                    continue;
                }
                $response->buildMacs($item['PartNumber'], $serial);
            }
        }
        
        return $response;
    }

    public function createOrder(CreateOrderParams $params): CreateOrderResponse
    {
        $request_xml = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
            <EnterOrder_v5 xmlns="http://WebService.jenne.com">
              <email>' . $this->username . '</email>
              <password>' . $this->password . '</password>
              <quoteNumber>' . $params->getQuoteNumber()  . '</quoteNumber>
              <poNumber>' . $params->getPoNumber() . '</poNumber>
              <endUserCompany>' . ($params->getContact()['company'] ?? '') . '</endUserCompany>
              <endUserAddress1>' . $params->getShippingAddress()['address1'] . '</endUserAddress1>
              <endUserAddress2>' . ($params->getShippingAddress()['address2'] ?? '') . '</endUserAddress2>
              <endUserCity>' . $params->getShippingAddress()['city'] . '</endUserCity>
              <endUserState>' . $params->getShippingAddress()['state'] . '</endUserState>
              <endUserPostalCode>' . $params->getShippingAddress()['zip'] . '</endUserPostalCode>
              <endUserCountry>' . $params->getShippingAddress()['country'] . '</endUserCountry>
              <endUserPhone>' . $params->getShippingAddress()['phone'] . '</endUserPhone>
              <paymentMethod>PO</paymentMethod>
              <creditCardId>0</creditCardId>
              <shippingSpecialInstr></shippingSpecialInstr>
              <backorderFlag>N</backorderFlag>
              <buyer></buyer>
              <endUserEmail></endUserEmail>
              <endUserPoNumber>' . $params->getPoNumber() . '</endUserPoNumber>
              <resellerAssignedCustomerId></resellerAssignedCustomerId>
              <shipToEmail></shipToEmail>
            </EnterOrder_v5>
          </soap:Body>
        </soap:Envelope>';

        $result = $this->doRequest('EnterOrder_v5', [], Request::HTTP_METHOD_POST, $request_xml);

        if (isset($result['error']) && !empty($result['error'])) {
            return new CreateOrderResponse('', false, $result, $result['error']);
        }

        return new CreateOrderResponse($result['OrderNumber'], empty($result['Error']['ErrorDescription']));
    }

    public function getCarrier($service_label): string
    {
        return $service_label;
    }

    public function getMethod($service_label): string
    {
        return $service_label;
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
            'Content-Type' => 'text/xml',
            'SOAPAction' => $this->soap_url . $endpoint
        ];

        $options = [
            'headers' => $headers, 'body' => $xml
        ];

        try {
            $response = $this->client->request(
                $method,
                '',
                $options
            );
        } catch (GuzzleException $exception) {
            $response = [
                'status' => $exception->getCode(),
                'error' => $exception->getMessage()
            ];
            $this->log($endpoint, $method, json_decode(json_encode(simplexml_load_string($xml)), true), $response);
            return $response;
        }

        $output = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response->getBody()->getContents());
        $output = json_decode(json_encode(simplexml_load_string($output)), true);

        $this->log($endpoint, $method, json_decode(json_encode(simplexml_load_string($xml)), true), $output);

        return $output['Body'][$endpoint . 'Response'][$endpoint . 'Result'];
    }

    protected function validateRequiredParams($params)
    {
        $required_keys = self::CREATE_ORDER_REQUIRED;
        $missing_required_keys = array_diff($required_keys, array_keys($params));
        if (!empty($missing_required_keys)) {
            throw new InvalidArgumentException(new Phrase('Missing required arguments: ' . implode(',', $missing_required_keys)), null, 422);
        }
    }
}

