<?php
namespace SkySwitch\Orders\Api;

/**
 * Interface WebhookInterface
 *
 * @api
 */
interface WebhookNtsInterface
{
    /**
     * Process distributor order information.
     *
     * @param string $data
     * @return void
     */
    public function processOrderInfo($data);
}
