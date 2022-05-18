<?php

namespace Voip888\Service\Interfaces;

interface Shipping888VoipInterface
{
    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @param string $value
     */
    public function setFirstName(string $value);

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @param string $value
     */
    public function setLastName(string $value);

    /**
     * @return string
     */
    public function getCompany();

    /**
     * @param string $value
     */
    public function setCompany(string $value);

    /**
     * @return string
     */
    public function getAddress1();

    /**
     * @param string $value
     */
    public function setAddress1(string $value);

    /**
     * @return string
     */
    public function getAddress2();

    /**
     * @param string $value
     */
    public function setAddress2(string $value);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $value
     */
    public function setCity(string $value);

    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $value
     */
    public function setState(string $value);

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @param string $value
     */
    public function setPostcode(string $value);

     /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $value
     */
    public function setCountry(string $value);
    
}
