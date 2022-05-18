<?php

namespace Voip888\Service\Interfaces;

interface Tracking888VoipInterface
{
    /**
     * @return string
     */
    public function getProvider();

    /**
     * @param string $value
     */
    public function setProvider(string $value);

    /**
     * @return string
     */
    public function getTrackingNumber();

    /**
     * @param string $value
     */
    public function setTrackingNumber(string $value);
}
