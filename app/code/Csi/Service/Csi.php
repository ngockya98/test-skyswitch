<?php

namespace Csi\Service;

use Exception;
use Magento\Framework\Webapi\Rest\Request;
use SkySwitch\Contracts\Traits\LogRequest;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class GitApiService
 */
class Csi
{
    use LogRequest;

    const TAX_EXEMPT_TYPES_MAPPING = [
        'F' => 'A',
        'S' => 'B',
        'C' => 'C',
        'L' => 'D',
        'FS' => 'E',
        'CF' => 'F',
        'FL' => 'G',
        'CS' => 'H',
        'LS' => 'I',
        'CL' => 'J',
        'CFS' => 'K',
        'FLS' => 'L',
        'CFL' => 'M',
        'CLS' => 'O',
        'CFLS' => 'Y',
    ];

    protected $access_code;
 
    protected $soap_client;

    public function __construct(array $credentials = []) {
        $object_manager = ObjectManager::getInstance();
        $soap_client_factory = $object_manager->create(ClientFactory::class);

        $this->access_code = $credentials['access_code'] ?? '';
        $this->base_url = $credentials['base_url'] ?? '';
        $path_to_wsdl = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'csi.wsdl';

        $this->soap_client = $soap_client_factory->create($path_to_wsdl);
    }

    public function getServiceName(): string
    {
        return 'CSI';
    }

    public function getTaxRatings($data)
	{
       $data = [
            "access_code" => $this->access_code,
            "reference" => "WebStore Tax - " . date("Y/m/d H:i:s"),
            "input" => $data
        ];

        return $this->doRequest('tax_rate', $data);
	}

    public function convertTaxExemptions($revio_tax_exempt_types) {
        //sort exempt types alphabetically
        $revio_tax_exempt_types = array_unique(str_split(strtoupper($revio_tax_exempt_types)));

        //CSI does not support city exemptions. If Rev.io has City exemption convert to Local exemption
        if (($key = array_search('I', $revio_tax_exempt_types)) !== false) {
            unset($revio_tax_exempt_types[$key]);
            $revio_tax_exempt_types[] = 'L';

            //deduplicate just in case revioTaxExemptTypes already had Local exemption.
            $revio_tax_exempt_types = array_unique($revio_tax_exempt_types);
        }

        sort($revio_tax_exempt_types);
        $revio_tax_exempt_types = implode('', $revio_tax_exempt_types);

        return self::TAX_EXEMPT_TYPES_MAPPING[$revio_tax_exempt_types] ?? '';

        switch($revio_tax_exempt_types) {
            case 'F':
                return 'A';
            case 'S':
                return 'B';
            case 'C':
                return 'C';
            case 'L':
                return 'D';
            case 'FS':
                return 'E';
            case 'CF':
                return 'F';
            case 'FL':
                return 'G';
            case 'CS':
                return 'H';
            case 'LS':
                return 'I';
            case 'CL':
                return 'J';
            case 'CFS':
                return 'K';
            case 'FLS':
                return 'L';
            case 'CFL':
                return 'M';
            case 'CLS':
                return 'O';
            case 'CFLS':
                return 'Y';
            default:
                return '';
        }
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
        array $params = []
    ) {


        try {
            $response = $this->soap_client->__soapCall($endpoint, [json_encode($params)]);
        } catch (Exception $exception) {

            $response = [
                'status' => $exception->getCode(),
                'error' => $exception->getMessage()
            ];
            $this->log($endpoint, Request::HTTP_METHOD_POST, $params, $response);
            return $response;
        }

        $output = json_decode($response, true);

        if ($output['status'] !== 'OK') {
            $response = [
                'status' => 400,
                'error' => $output['error_codes'][0]['error_code']
            ];
            $this->log($endpoint, Request::HTTP_METHOD_POST, $params, $response);
            return $response;
        }

        $this->log($endpoint, Request::HTTP_METHOD_POST, $params, $output);

        return $output;
    }
}

