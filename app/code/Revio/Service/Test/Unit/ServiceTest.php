<?php
 
namespace Revio\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use Revio\Service\Revio;

class ServiceTest extends TestCase
{
    protected $service;

    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new Revio($environment['revio']);
    }
 
    public function testGetCustomers(): void
    {
        $result = $this->service->getCustomers(['account_number' => '20083']);

        $this->assertTrue($result['ok']);
        $this->assertEquals(1, $result['record_count']);
        $this->assertEquals('20083', $result['records'][0]['account_number']);
    }

    public function testgetPaymentAccounts(): void
    {
        $result = $this->service->getPaymentAccounts(['customer_id' => '1022']);

        $this->assertTrue($result['ok']);
    }

    public function testGetResellerPaymentAccounts(): void
    {
        $result = $this->service->getResellerPaymentAccounts('20083');

        $this->assertNotEmpty($result);
    }
}