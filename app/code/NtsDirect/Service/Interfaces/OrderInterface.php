<?php
namespace NtsDirect\Service\Interfaces;

interface OrderInterface
{
    /**
     * @return string
     */
    public function getOrderNumber();

    /**
     * @param string $value
     */
    public function setOrderNumber(string $value);

    /**
     * @return string
     */
    public function getOrderDate();

    /**
     * @param string $value
     */
    public function setOrderDate(string $value);

    /**
     * @return string
     */
    public function getDateShipped();

    /**
     * @param string $value
     */
    public function setDateShipped(string $value);

    /**
     * @return string
     */
    public function getCustomerCode();

    /**
     * @param string $value
     */
    public function setCustomerCode(string $value);

    /**
     * @return string
     */
    public function getShipToName();

    /**
     * @param string $value
     */
    public function setShipToName(string $value);

    /**
     * @return string
     */
    public function getShipToAddress1();

    /**
     * @param string $value
     */
    public function setShipToAddress1(string $value);

    /**
     * @return string
     */
    public function getShipToAddress2();

    /**
     * @param string $value
     */
    public function setShipToAddress2(string $value);

    /**
     * @return string
     */
    public function getShipToCity();

    /**
     * @param string $value
     */
    public function setShipToCity(string $value);

    /**
     * @return string
     */
    public function getShipToState();

    /**
     * @param string $value
     */
    public function setShipToState(string $value);

    /**
     * @return string
     */
    public function getShipToZip();

    /**
     * @param string $value
     */
    public function setShipToZip(string $value);

    /**
     * @return string
     */
    public function getShipToCountry();

    /**
     * @param string $value
     */
    public function setShipToCountry(string $value);

    /**
     * @return string
     */
    public function getShipVia();

    /**
     * @param string $value
     */
    public function setShipVia(string $value);

    /**
     * @return string
     */
    public function getTrackingNo();

    /**
     * @param string $value
     */
    public function setTrackingNo(string $value);

    /**
     * @return NtsDirect\Service\Interfaces\ItemInterface[]
     */
    public function getItems();

    /**
     * @param NtsDirect\Service\Interfaces\ItemInterface[] $value
     */
    public function setItems(array $value);
}
