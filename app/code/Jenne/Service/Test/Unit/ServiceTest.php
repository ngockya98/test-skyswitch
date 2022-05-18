<?php
 
namespace Jenne\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use Jenne\Service\Jenne;
use SkySwitch\Contracts\GetShippingRatesParams;

class ServiceTest extends TestCase
{
    protected $service;

    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new Jenne($environment['jenne']);
    }
 
    public function testGetProducts(): void
    {
        $result = $this->service->getProduct('test');

        $this->assertNotEmpty($result['Products']['Product']);
        $this->assertEquals('0', $result['Error']['ErrorNumber']);
    }

    public function testCheckStock(): void
    {
        $result = $this->service->checkStock('6867-07998-202');

        $this->assertEquals('200', $result->getStatus());
    }

    public function testGetShippingRates(): void 
    {

        $params = new GetShippingRatesParams();
        $params->setShippingAddress('4594 Hott Street', '', 'Oklahoma City', 'OK', 'US', '73119', '7865555555');
        $params->setContact('Test Quote', '7865555555');
        $params->addItem('SIP-VP59-WITHPS', '1');
        
        $result = $this->service->getShippingRates($params)->getRates();

        $this->assertEquals(count($result), 4); 
        $this->assertEquals($result[0]['service_label'], 'FedEx Priority Overnight');
    }

    public function testGetOrder_gets_all_orders(): void
    {
        $result = $this->service->getOrders();

        $this->assertEquals('0', $result['Error']['ErrorNumber']);
        $this->assertTrue(isset($result['Invoices']['InvoiceV2'])); 
        $this->assertNotEmpty($result['Invoices']['InvoiceV2']);
    }

    public function testGetOrder_gets_only_one_order(): void
    {
        $result = $this->service->getOrders(['order_id' => '133855']);

        $this->assertEquals('0', $result['Error']['ErrorNumber']);
        $this->assertTrue(isset($result['Invoices']['InvoiceV2'])); 
        $this->assertEquals('133855', $result['Invoices']['InvoiceV2']['PONumber']);
    }

    public function testGetOrder_gets_zero_orders_after_today(): void
    {
        $result = $this->service->getOrders(['start_date' => date('Y-m-d')]);

        $this->assertEquals('0', $result['Error']['ErrorNumber']);
        $this->assertFalse(isset($result['Invoices']['InvoiceV2'])); 
    }

    public function testGetOrder_gets_zero_orders_before(): void
    {
        $result = $this->service->getOrders(['end_date' => date('2000-01-01')]);

        $this->assertEquals('0', $result['Error']['ErrorNumber']);
        $this->assertFalse(isset($result['Invoices']['InvoiceV2'])); 
    }

}