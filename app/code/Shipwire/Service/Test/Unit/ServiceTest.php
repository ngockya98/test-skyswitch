<?php
 
namespace Shipwire\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use Shipwire\Service\Shipwire;
use SkySwitch\Contracts\GetShippingRatesParams;

class ServiceTest extends TestCase
{
    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new Shipwire($environment['shipwire']);
    }
    
    /** @test */
    public function it_returns_stock_for_all_products_with_pagination(): void
    {
        $result = $this->service->checkStocks();

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['resource']['items']);
        $this->assertEquals(0, $result['resource']['offset']);
    }

    /** @test */
    public function it_returns_stock_for_specific_sku(): void
    {
        $result = $this->service->checkStock('iXEdge1000L3Y-MC');

        $this->assertEquals(200, $result->getStatus());
    }

    /** @test */
    public function it_returns_shipping_rates(): void
    {
        $params = new GetShippingRatesParams();
        $params->setShippingAddress('6501 Railroad Avenue SE', 'Room 315', 'Snoqualmie', 'WA', 'US', '85283', '');
        $params->addItem('2272E4-MC', '1');

        $result = $this->service->getShippingRates($params)->getRates();

        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_gets_all_orders_with_pagination(): void
    {
        $result = $this->service->getOrders();

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['resource']['items']);
        $this->assertEquals(0, $result['resource']['offset']);
        $this->assertEquals(100, count($result['resource']['items']));
    }

    /** @test */
    public function it_gets_an_order(): void
    {
        $result = $this->service->getOrder('152500812');

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['resource']);
        $this->assertEquals(152500812, $result['resource']['id']);
    }

    /** @test */
    public function it_gets_an_order_items(): void
    {
        $result = $this->service->getOrderItems('152500812');

        $this->assertEquals(200, $result['status']);
        $this->assertStringContainsString('152500812', $result['resourceLocation']);
        $this->assertCount(1, $result['resource']['items']);
    }

    /** @test */
    public function it_gets_all_carriers_with_pagination(): void
    {
        $result = $this->service->getCarriers();

        $this->assertEquals(200, $result['status']);
        $this->assertNotEmpty($result['resource']['items']);
        $this->assertEquals(0, $result['resource']['offset']);
        $this->assertEquals(30, count($result['resource']['items']));
    }

    /** @test */
    public function it_gets_tracking_info_with_order_id(): void
    {
        $result = $this->service->getTrackingInfo('152500812');

        $this->assertEquals(200, $result['status']);
    }

}