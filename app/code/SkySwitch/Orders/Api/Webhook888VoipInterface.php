<?php
namespace SkySwitch\Orders\Api;

/**
 * Interface WebhookInterface
 *
 * @api
 */
interface Webhook888VoipInterface
{
    /**
     * Process distributor order information.
     *
     * @param \Voip888\Service\Interfaces\Request888VoipInterface $order
     * @return void
     */
    public function processOrderInfo($order);
}
