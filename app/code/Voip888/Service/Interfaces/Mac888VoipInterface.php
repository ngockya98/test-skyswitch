<?php

namespace Voip888\Service\Interfaces;

interface Mac888VoipInterface
{
    /**
     * @return string
     */
    public function getSerial();

    /**
     * @param string $value
     */
    public function setSerial(string $value);

    /**
     * @return string
     */
    public function getMac();

    /**
     * @param string $value
     */
    public function setMac(string $value);
}
