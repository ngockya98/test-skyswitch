<?php

namespace Revio\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Phrase;
use Revio\Service\Exception\BadRequestException;
use Revio\Service\Exception\InvalidArgumentException;
use Revio\Service\Exception\NotFoundException;
use SkySwitch\Contracts\Traits\LogRequest;

/**
 * Class GitApiService
 */
class Revio
{
    use LogRequest;

    const CREATE_ACH_REQUIRED_PARAMS =  [
        'account_name',
        'account_number',
        'routing_number',
        'account_type',
        'customer_id'
    ];

    const CREATE_CC_REQUIRED_PARAMS =  [
        'name_first',
        'name_last',
        'card_number',
        'expiration_month',
        'expiration_year',
        'cvv',
        'customer_id'
    ];

    const ACH = 'Ach';

    const CC = 'Cc';

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
    }

    public function getServiceName(): string
    {
        return 'Rev.io';
    }

    public function getResellerPaymentAccounts(string $reseller_id, array $filters = [])
    {
        $response = $this->getCustomers(['account_number' => $reseller_id]);

        if ((isset($response['error']) && !empty($response['error'])) || empty($response['records'])) {
            return [];
        }

        $customer_id = $response['records'][0]['customer_id'];

        $response = $this->getPaymentAccounts(array_merge($filters, ['customer_id' => $customer_id]));

        if ((isset($response['error']) && !empty($response['error'])) || empty($response['records'])) {
            return [];
        }

        return ['records' => $response['records'], 'customer_id' => $customer_id];
        
    }

    public function getTaxExemption(string $reseller_id)
    {
        $response = $this->getCustomers(['account_number' => $reseller_id]);

        if ((isset($response['error']) && !empty($response['error'])) || empty($response['records'])) {
            return [
                'tax_exempt' => false,
                'tax_exempt_types' => '',
                'tax_zip' => ''
            ];
        }

        return [
            'tax_exempt' => $response["records"][0]["finance"]["tax_exempt_enabled"],
            'tax_exempt_types' => $response["records"][0]["finance"]["tax_exempt_types"],
            'tax_zip' => $response["records"][0]["billing_address"]["postal_code"]
        ];
    }

    public function getCustomers(array $filters)
    {
        $query_string = $this->buildQueryParams($filters);
        
        return $this->doRequest('Customers' . $query_string);
    }

    public function getPaymentAccounts(array $filters)
    {
        $query_string = $this->buildQueryParams($filters);
        
        return $this->doRequest('PaymentAccounts' . $query_string);
    }

    public function createAchPaymentAccount(array $params, string $reseller_id)
    {   
        $this->validateRequiredParams(self::CREATE_ACH_REQUIRED_PARAMS, $params);

        return $this->doRequest('PaymentAccounts/bankaccount', $params, Request::HTTP_METHOD_POST);
    }

    public function createCcPaymentAccount(array $params, string $reseller_id)
    {   
        $response = $this->getCustomers(['account_number' => $reseller_id]);

        if ((isset($response['error']) && !empty($response['error'])) || empty($response['records'])) {
            throw new BadRequestException(new Phrase($response['error']), null, 500);
        }

        $customer_id = $response['records'][0]['customer_id'];

        $params['customer_id'] = $customer_id;

        $this->validateRequiredParams(self::CREATE_CC_REQUIRED_PARAMS, $params);
        
        return $this->doRequest('PaymentAccounts/card', $params, Request::HTTP_METHOD_POST);
    }

    public function createPaymentAccount(array $params, string $type, string $reseller_id)
    {   
        $method = 'Create' . $type . 'PaymentAccount';
        return $this->$method($params, $reseller_id);
    }

    public function deletePaymentAccount(string $last_4, string $first_name, string $last_name, string $reseller_id)
    {   
        $filters = [
            'card_name_first' => $first_name,
            'card_name_last' => $last_name,
            'last_4' => $last_4
        ];

        $response = $this->getResellerPaymentAccounts($reseller_id, $filters);

        if (empty($response['records']) || count($response['records']) > 1) {
            throw new NotFoundException(new Phrase('Payment account not found in Rev.io.'), null, 404);
        }

        return $this->doRequest('PaymentAccounts/' . $response['records'][0]['payment_account_id'], [], Request::HTTP_METHOD_DELETE);

    }

    public function updatePaymentAccount(array $old_card_data, array $new_card_data, string $reseller_id, $type = self::CC)
    {
        $filters = [
            'card_name_first' => $old_card_data['firstname'] ?? '',
            'card_name_last' => $old_card_data['lastname'] ?? '',
            'last_4' => $old_card_data['cc_last_4'] ?? ''
        ];

        $reseller_payment_accounts = $this->getResellerPaymentAccounts($reseller_id, $filters);

        if (empty($reseller_payment_accounts['records']) || count($reseller_payment_accounts['records']) > 1) {
            throw new NotFoundException(new Phrase('Payment account not found in Rev.io.'), null, 404);
        }

        $new_card_data['customer_id'] = $reseller_payment_accounts['customer_id'];
        $this->validateRequiredParams(self::CREATE_CC_REQUIRED_PARAMS, $new_card_data);
      

        if (isset($new_card_data['card_number']) && !empty($new_card_data['card_number'])) {
            // Delete old card and create a new one
            try {
                $this->deletePaymentAccount($old_card_data['cc_last_4'] ?? '', $old_card_data['firstname'] ?? '', $old_card_data['lastname'] ?? '', $reseller_id);
            } catch (NotFoundException $exception) {

            }

            return $this->createPaymentAccount($new_card_data, $type, $reseller_id);
        }

        // Replace card
        if ($type === self::CC) {
            return $this->replaceCreditCard($reseller_payment_accounts['records'][0], $new_card_data, $old_card_data);
        }
    }

    public function replaceCreditCard(array $payment_account, array $new_card_data, array $old_card_data)
    {
        $new_card_data['expiration_month'] = $old_card_data['cc_exp_month'];
        $new_card_data['expiration_year'] = $old_card_data['cc_exp_year'];
        $new_card_data['payment_account_id'] = $payment_account['payment_account_id'];

        unset($new_card_data['card_number']);
        $result = $this->doRequest('PaymentAccounts/card/' . $payment_account['payment_account_id'], $new_card_data, Request::HTTP_METHOD_PUT);

        return $result;
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

        $headers['Authorization'] = 'Basic ' . base64_encode($this->username . ':' . $this->password);

        $options['headers'] = $headers;
        if (!empty($params)) {
            $options['json'] = $params;
        }

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

        $output = json_decode($response->getBody()->getContents(), true);
        $this->log($endpoint, $method, $params, $output);

        return $output;
    }

    protected function buildQueryParams(array $params)
    {
        if (empty($params)) {
            return '?search.page=1&search.page_size=1000';
        }

        $query_string = '';

        $i = 0;
        foreach ($params as $name => $value) {
            if ($i === 0) {
                $query_string = '?search.' . $name . '=' . $value;
                $i++;
                continue;
            }

            $query_string .= '&search.' . $name . '=' . $value;
            $i++;
        }

        return $query_string . '&search.page=1&search.page_size=1000';
    }

    protected function validateRequiredParams($required_keys, $params)
    {
        $missing_required_keys = array_diff($required_keys, array_keys($params));
        if (!empty($missing_required_keys)) {
            throw new InvalidArgumentException(new Phrase('Missing required arguments: ' . implode(',', $missing_required_keys)), null, 422);
        }
    }
}

