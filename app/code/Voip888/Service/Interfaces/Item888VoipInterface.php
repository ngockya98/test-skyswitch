<?php

namespace Voip888\Service\Interfaces;

interface Item888VoipInterface
{
    /**
     * @return string
     */
    public function getLineNumber();

    /**
     * @param string $value
     */
    public function setLineNumber(string $value);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $value
     */
    public function setSku(string $value);

    /**
     * @return int
     */
    public function getQty();

    /**
     * @param int $value
     */
    public function setQty(int $value);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $value
     */
    public function setPrice(float $value);

    /**
     * @return \Voip888\Service\Interfaces\Mac888VoipInterface[]
     */
    public function getSerialsAndMacs();

    /**
     * @param \Voip888\Service\Interfaces\Mac888VoipInterface[] $value
     */
    public function setSerialsAndMacs($value);
    
}
