<?php
namespace Voip888\Service\Resources;

use Voip888\Service\Interfaces\Mac888VoipInterface;

class Mac888Voip implements Mac888VoipInterface
{
    public array $data;

    public function getSerial()
    {
        return $this->data['serial'];
    }

    public function setSerial(string $value)
    {
        $this->data['serial'] = $value;
    }

    /**
     * @return string
     */
    public function getMac()
    {
        return $this->data['mac'];
    }

    /**
     * @param string $value
     */
    public function setMac(string $value)
    {
        $this->data['mac'] = $value;
    }
}
