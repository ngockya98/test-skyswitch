<?php
namespace SkySwitch\Orders\Api;

/**
 * Interface WebhookInterface
 *
 * @api
 */
interface WebhookJenneInterface
{
    /**
     * Process distributor order information.
     *
     * @return void
     */
    public function processOrderInfo();
}
