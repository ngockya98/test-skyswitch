<?php
namespace Voip888\Service\Resources;

use Voip888\Service\Interfaces\Provisioning888VoipInterface;

class Provisioning888Voip implements Provisioning888VoipInterface
{
    public array $data;

    public function getSku()
    {
        return $this->data['sku'];
    }

    public function setSku(string $value)
    {
        $this->data['sku'] = $value;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->data['qty'];
    }

    /**
     * @param int $value
     */
    public function setQty(int $value)
    {
        $this->data['qty'] = $value;
    }

    public function getProvUrl()
    {
        return $this->data['prov_url'];
    }

    public function setProvUrl(string $value)
    {
        $this->data['prov_url'] = $value;
    }

    public function getSrvPass()
    {
        return $this->data['srv_pass'];
    }

    public function setSrvPass(string $value)
    {
        $this->data['srv_pass'] = $value;
    }

    public function getSrvUser()
    {
        return $this->data['srv_user'];
    }

    public function setSrvUser(string $value)
    {
        $this->data['srv_user'] = $value;
    }
}
