<?php
namespace Voip888\Service\Resources;

use Voip888\Service\Interfaces\Item888VoipInterface;

class Item888Voip implements Item888VoipInterface
{
    public array $data;

    public function getLineNumber()
    {
        return $this->data['LineNumber'];
    }

    public function setLineNumber(string $value)
    {
        $this->data['LineNumber'] = $value;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->data['sku'];
    }

    /**
     * @param string $value
     */
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
        $this->data['Qqy'] = $value;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->data['price'];
    }

    /**
     * @param float $value
     */
    public function setPrice(float $value)
    {
        $this->data['price'] = $value;
    }

    /**
     * @return \Voip888\Service\Interfaces\Mac888VoipInterface[]
     */
    public function getSerialsAndMacs()
    {
        return $this->data['serialsAndMacs'];
    }

    /**
     * @param \Voip888\Service\Interfaces\Mac888VoipInterface[] $value
     */
    public function setSerialsAndMacs($value)
    {
        $this->data['serialsAndMacs'] = $value;
    }
}
