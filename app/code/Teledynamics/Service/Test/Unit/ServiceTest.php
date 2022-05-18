<?php
 
namespace Teledynamics\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use SkySwitch\Contracts\CreateOrderParams;
use SkySwitch\Contracts\GetShippingRatesParams;
use Teledynamics\Service\Teledynamics;

class ServiceTest extends TestCase
{
    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new Teledynamics($environment['teledynamics']);
    }
 
    public function testGetProduct(): void
    {
        $result = $this->service->getProduct('YEA-SIP-T54W');

        $this->assertNotEmpty($result);
        $this->assertEquals('YEA-SIP-T54W', $result['PartNumber']);
    }

    public function testCheckStock(): void
    {
        $result = $this->service->checkStock('YEA-SIP-T54W');

        $this->assertTrue(!is_null($result->getStock()));
        $this->assertTrue(!is_null($result->getPrice()));
    }

    public function testGetManufacturerProducts(): void
    {
        $result = $this->service->getManufacturerProducts('Yealink');
        $filtered = array_filter($result, function($product) {
            return $product['Manufacturer'] == 'Yealink';
        });

        $this->assertEquals('Yealink', $result[0]['Manufacturer']);
        $this->assertNotEmpty($result);
        $this->assertCount(count($result), $filtered);
    }

    public function testGetShippingRates(): void
    {
        $params = new GetShippingRatesParams();
        $params->setPoNumber('SKY8700-' . date('His'));
        $params->setProvision(false);
        $params->setShippingAddress('2907  Green Acres Road', '', 'Havelock', 'NC', 'USA', '28532', '', 'Residential');
        $params->addItem('YEA-SIP-T54W', '1');

        $result = $this->service->getShippingRates($params)->getRates();

        $this->assertNotEmpty($result);
    }

    public function testCreateOrder(): void
    {
        $params = new CreateOrderParams();
        $params->setPoNumber('SKY8700-' . date('His'));
        $params->setProvision(false);
        $params->setShippingAddress('2907  Green Acres Road', '', 'Havelock', 'NC', 'USA', '28532', '', 'Residential');
        $params->setShippingCarrier('Fedex');
        $params->setShippingMethod('FED_2DY');
        $params->addItem('YEA-SIP-T54W', '1');

        $result = $this->service->createOrder($params);
       
        $this->assertTrue($result->isSuccessful());
        $this->assertNotEmpty($result->getOrderId());
    }

}
