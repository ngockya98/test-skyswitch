<?php
namespace NtsDirect\Service\Model\Api;

use NtsDirect\Service\Interfaces\ItemInterface;

class Item implements ItemInterface
{
    protected $data;

   /**
     * @return string
     */
    public function getPartNumber()
    {
        return $this->data['PartNumber'];
    }

    /**
     * @param string $value
     */
    public function setPartNumber(string $value) 
    {
        $this->data['PartNumber'] = $value;
    }

    /**
     * @return string
     */
    public function getSerialNo()
    {
        return $this->data['SerialNo'];
    }

    /**
     * @param string $value
     */
    public function setSerialNo(string $value)
    {
        $this->data['SerialNo'] = $value;
    }

    /**
     * @return string
     */
    public function getMACAddress()
    {
        return $this->data['MACAddress'];
    }

    /**
     * @param string $value
     */
    public function setMACAddress(string $value)
    {
        $this->data['MACAddress'] = $value;
    }
}
