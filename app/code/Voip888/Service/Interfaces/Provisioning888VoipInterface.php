<?php

namespace Voip888\Service\Interfaces;

interface Provisioning888VoipInterface
{
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
     * @return string
     */
    public function getProvUrl();

    /**
     * @param string $value
     */
    public function setProvUrl(string $value);

    /**
     * @return string
     */
    public function getSrvPass();

    /**
     * @param string $value
     */
    public function setSrvPass(string $value);

    /**
     * @return string
     */
    public function getSrvUser();

    /**
     * @param string $value
     */
    public function setSrvUser(string $value);
}
