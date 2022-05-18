<?php
namespace NtsDirect\Service\Interfaces;

interface ItemInterface
{
    /**
     * @return string
     */
    public function getPartNumber();

    /**
     * @param string $value
     */
    public function setPartNumber(string $value);

    /**
     * @return string
     */
    public function getSerialNo();

    /**
     * @param string $value
     */
    public function setSerialNo(string $value);

    /**
     * @return string
     */
    public function getMACAddress();

    /**
     * @param string $value
     */
    public function setMACAddress(string $value);

}
