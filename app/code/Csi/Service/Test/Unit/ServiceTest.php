<?php
 
namespace Csi\Service\Test\Unit;

use PHPUnit\Framework\TestCase;
use Csi\Service\Csi;

class ServiceTest extends TestCase
{
    protected $service;

    public function setUp() : void
    {
        $json = file_get_contents(__DIR__ . '/resource.json');
        $environment = json_decode($json,true);

        $this->service = new Csi($environment['csi']);
    }
 
    public function testGetRatings(): void
    {
        $params = [
            [
                "unique_id"      => 'reseller_store_TESTSKU_' . time(),
                "record_type"    => "S",
                "keep_record"    => 1,
                "account_number" => '20083',
                "customer_type"  => "1",
                "invoice_date"   => date( "Ymd" ),
                "invoice_number" => "1",
                "location_a"     => '33145',
                "productcode"    => 'G001',
                "servicecode"    => '1',
                "charge_amount"  => 9.99,
                "units"          => 1,
                "exempt_code"    => 'A'
            ]
        ];

        $result = $this->service->getTaxRatings($params);
        
        $this->assertCount(2, $result['tax_data']);
        $this->assertEquals($result['tax_data'][0]['geo_county'], 'Miami-Dade');
        $this->assertEquals($result['tax_data'][1]['geo_county'], 'Miami-Dade');
        $this->assertEquals($result['tax_data'][0]['geo_city'], 'Miami');
        $this->assertEquals($result['tax_data'][1]['geo_city'], 'Miami');
    }
}