<?php
 
namespace Voip888\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use SkySwitch\Contracts\CreateOrderParams;
use SkySwitch\Contracts\GetShippingRatesParams;
use Voip888\Service\Voip888;

class ServiceTest extends TestCase
{
    protected $service;
    protected $order_id;

    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new Voip888($environment['888voip']);

        $this->order_id = null;
    }

    public function testGetToken(): void
    {
        $result = $this->service->getToken();

        $this->assertNotNull($result);
    }
 
    public function testGetProducts(): void
    {
        $result = $this->service->getProduct();

        $this->assertNotEmpty($result['products']);
        $this->assertEquals('success', $result['response']);
    }

    public function testGetProduct(): void
    {
        $result = $this->service->getProduct('YEA-WH63-TEAMS');

        $this->assertNotEmpty($result['product']);
        $this->assertEquals('success', $result['response']);
        $this->assertEquals('YEA-WH63-TEAMS', $result['product']['sku']);
    }

    public function testGetShippingRates(): void 
    {
        $params = new GetShippingRatesParams();
        $params->setShippingAddress('Test Address', 'Suite 102', 'Test City', 'CA', 'US', '90249', '', '');
        $params->addItem('EME-HMGLXC-4S', '1');
        
        $result = $this->service->getShippingRates($params)->getRates();

        $this->assertNotEmpty($result);
    }

    public function testCreateOrder(): void
    {
        if (!is_null($this->order_id)) {
            return;
        }

        $params = new CreateOrderParams();
        $params->setShippingAddress('1973 Oak Drive', '', 'Albany', 'NY', 'US', '12207', '716-555-5555');
        $params->setBillingAddress('1973 Oak Drive', '', 'Albany', 'NY', 'US', '12207', '716-555-5555');
        $params->setPoNumber('542783551');
        $params->setShippingMethod('FedEx Ground');
        $params->setContact('Test Company', 'First', 'Last');
        $params->addItem('AMT-AT510DS', '1');

        $result = $this->service->createOrder($params);

        $this->assertTrue($result->isSuccessful());
    }

    public function testGetOrder(): void
    {
        $result = $this->service->getOrders(['order_id' => '119857']);

        $this->assertEquals('success', $result['response']);
        $this->assertTrue(isset($result['order']));
        $this->assertNotEmpty($result['order']);
        $this->assertEquals('119857', $result['order']['orderNumber']);
    }

}