<?php
namespace NtsDirect\Service\Model\Api;

use NtsDirect\Service\Interfaces\OrderInterface;

class Order implements OrderInterface
{
    protected $data;

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->data['OrderNumber'];
    }

    /**
     * @param string $value
     */
    public function setOrderNumber(string $value)
    {
        $this->data['OrderNumber'] = $value;
    }

    /**
     * @return string
     */
    public function getOrderDate()
    {
        return $this->data['OrderDate'];
    }

    /**
     * @param string $value
     */
    public function setOrderDate(string $value)
    {
        $this->data['OrderDate'] = $value;
    }

    /**
     * @return string
     */
    public function getDateShipped()
    {
        return $this->data['DateShipped'];
    }

    /**
     * @param string $value
     */
    public function setDateShipped(string $value)
    {
        $this->data['DateShipped'] = $value;
    }

    /**
     * @return string
     */
    public function getCustomerCode()
    {
        return $this->data['CustomerCode'];
    }

    /**
     * @param string $value
     */
    public function setCustomerCode(string $value)
    {
        $this->data['CustomerCode'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToName()
    {
        return $this->data['ShipToName'];
    }

    /**
     * @param string $value
     */
    public function setShipToName(string $value)
    {
        $this->data['ShipToName'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToAddress1()
    {
        return $this->data['ShipToAddress1'];
    }

    /**
     * @param string $value
     */
    public function setShipToAddress1(string $value)
    {
        $this->data['ShipToAddress1'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToAddress2()
    {
        return $this->data['ShipToAddress2'];
    }

    /**
     * @param string $value
     */
    public function setShipToAddress2(string $value)
    {
        $this->data['ShipToAddress2'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToCity()
    {
        return $this->data['ShipToCity'];
    }

    /**
     * @param string $value
     */
    public function setShipToCity(string $value)
    {
        $this->data['ShipToCity'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToState()
    {
        return $this->data['ShipToState'];
    }

    /**
     * @param string $value
     */
    public function setShipToState(string $value)
    {
        $this->data['ShipToState'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToZip()
    {
        return $this->data['ShipToZip'];
    }

    /**
     * @param string $value
     */
    public function setShipToZip(string $value)
    {
        $this->data['ShipToZip'] = $value;
    }

    /**
     * @return string
     */
    public function getShipToCountry()
    {
        return $this->data['ShipToCountry'];
    }

    /**
     * @param string $value
     */
    public function setShipToCountry(string $value)
    {
        $this->data['ShipToCountry'] = $value;
    }

    /**
     * @return string
     */
    public function getShipVia()
    {
        return $this->data['ShipVia'];
    }

    /**
     * @param string $value
     */
    public function setShipVia(string $value)
    {
        $this->data['ShipVia'] = $value;
    }

    /**
     * @return string
     */
    public function getTrackingNo()
    {
        return $this->data['TrackingNo'];
    }

    /**
     * @param string $value
     */
    public function setTrackingNo(string $value)
    {
        $this->data['TrackingNo'] = $value;
    }

    /**
     * @return NtsDirect\Service\Interfaces\ItemInterface[]
     */
    public function getItems()
    {
        return $this->data['Items'];
    }

    /**
     * @param NtsDirect\Service\Interfaces\ItemInterface[] $value
     */
    public function setItems(array $value)
    {
        $this->data['Items'] = $value;
    }
}
