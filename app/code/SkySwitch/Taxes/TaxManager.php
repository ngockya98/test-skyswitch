<?php

namespace SkySwitch\Taxes;

use Csi\Service\Csi;
use Magento\Framework\App\DeploymentConfig;
use Revio\Service\Revio;
use SkySwitch\Distributors\Managers\DistributorManager;

class TaxManager
{
    const HARDWARE = 'hardware_type'; //phpcs:ignore
    const SOFTWARE = 'software_type'; //phpcs:ignore
    const HARDWARE_TAX_GROUP_CODE = 'G001'; //phpcs:ignore
    const HARDWARE_TAX_ITEM_CODE = '1'; //phpcs:ignore
    const SOFTWARE_TAX_GROUP_CODE = 'C001'; //phpcs:ignore
    const SOFTWARE_TAX_ITEM_CODE = '9'; //phpcs:ignore

    /**
     * @var DeploymentConfig
     */
    protected $deployment_config;

    /**
     * @var Revio
     */
    protected $revio_service;

    /**
     * @var Csi
     */
    protected $csi_service;

    /**
     * @param DeploymentConfig $deployment_config
     */
    public function __construct(DeploymentConfig $deployment_config)
    {
        $this->deployment_config = $deployment_config;
        $this->revio_service = new Revio($this->deployment_config->get('services/revio'));
        $this->csi_service = new Csi($this->deployment_config->get('services/csi'));
    }

    /**
     * Process calculate taxes
     *
     * @param mixed $items
     * @param mixed $reseller_id
     * @return int|mixed
     */
    public function calculateTaxes($items, $reseller_id)
    {
        $tax_total_amount = 0;
        $tax_data = $this->revio_service->getTaxExemption($reseller_id);
        $tax_zip = $tax_data["tax_zip"] ?? '';
        $tax_exempt_types = $tax_data["tax_exempt_types"];
        $csi_exempt_code = $this->csi_service->convertTaxExemptions($tax_exempt_types);

        if (!empty($tax_zip)) {
            $tax_input = [];

            foreach ($items as $item) {
                $product = $item->getProduct();
                $product_tax_group_code = $product->getTypeId() === self::SOFTWARE
                    ? self::SOFTWARE_TAX_GROUP_CODE : self::HARDWARE_TAX_GROUP_CODE;
                $product_tax_item_code  = $product->getTypeId() === self::SOFTWARE
                    ? self::SOFTWARE_TAX_ITEM_CODE : self::HARDWARE_TAX_ITEM_CODE;

                $sku = $item->getData('sku');

                $tax_input_single = [
                    "unique_id"      => "reseller_store_" . $sku . '_' . time(),
                    "record_type"    => "S",
                    "keep_record"    => 1,
                    "account_number" => $reseller_id,
                    "customer_type"  => "1",
                    "invoice_date"   => date("Ymd"),
                    "invoice_number" => "1",
                    "location_a"     => $tax_zip,
                    "productcode"    => $product_tax_group_code,
                    "servicecode"    => $product_tax_item_code,
                    "charge_amount"  => round($item->getPrice(), 2),
                    "units"          => $item->getQty(),
                    "exempt_code"    => $csi_exempt_code,
                ];
                array_push($tax_input, $tax_input_single);
            }
            if (count($tax_input) > 0) {
                $tax_response = $this->csi_service->getTaxRatings($tax_input);

                if (isset($tax_response['tax_data'])) {
                    foreach ($tax_response['tax_data'] as $tax_record) {
                        if (isset($tax_record['taxamount'])) {
                            $tax_total_amount = $tax_total_amount + $tax_record['taxamount'];
                        }
                    }
                }
            }
        }

        return $tax_total_amount;
    }
}
