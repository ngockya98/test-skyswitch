<?php
namespace Voip888\Service\Resources;

use Voip888\Service\Interfaces\Tracking888VoipInterface;

class Tracking888Voip implements Tracking888VoipInterface
{
    public array $data;

    public function getProvider()
    {
        return $this->data['provider'];
    }

    public function setProvider(string $value)
    {
        $this->data['provider'] = $value;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->data['trackingNumber'];
    }

    /**
     * @param string $value
     */
    public function setTrackingNumber(string $value)
    {
        $this->data['trackingNumber'] = $value;
    }
}
