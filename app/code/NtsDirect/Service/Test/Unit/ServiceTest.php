<?php
 
namespace NtsDirect\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use NtsDirect\Service\NtsDirect;
use SkySwitch\Contracts\GetShippingRatesParams;

class ServiceTest extends TestCase
{
    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new NtsDirect($environment['nts_direct']);
    }
 
    public function testGetProduct(): void
    {
        $result = $this->service->getProduct('DP722');

        $this->assertCount(1, $result['ItemRequestResponse']);
        $this->assertEquals('DP722', $result['ItemRequestResponse']['Items']['MFGPartNumber']);
    }

    public function testGetShippingRates(): void 
    {
        $params = new GetShippingRatesParams();
        $params->setShippingAddress('2925 Union Street', '', 'SPOKANE', 'WA', 'US', '99214', '');
        $params->setContact('hghg', '(786) 987-4566', 'dgdgd@gmail.com');
        $params->addItem('DP722', '1');
        $params->addItem('DP730', '1');

        $result = $this->service->getShippingRates($params)->getRates();

        $this->assertNotEmpty($result);
        $this->assertEquals('Fedex Ground', $result[0]['service_label']);  
    }

    public function testGetOrdersNoOrdersTomorrow(): void
    {
        $start = strtotime("+1 day");
        $result = $this->service->getOrders(['start' => $start]);

        $this->assertEquals(404, $result['status']);
        $this->assertStringContainsString('Order Not Found', $result['error']);
    }

    public function testGetOrdersFound(): void
    {
        $result = $this->service->getOrders(['start' => '1596312351']);

        $this->assertNotEmpty($result);
    }

}
