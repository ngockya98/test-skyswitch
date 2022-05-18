<?php
namespace Voip888\Service\Resources;

use Voip888\Service\Interfaces\Shipping888VoipInterface;

class Shipping888Voip implements Shipping888VoipInterface
{
    public array $data;

    public function getFirstName()
    {
        return $this->data['firstName'];
    }

    public function setFirstName(string $value)
    {
        $this->data['firstName'] = $value;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->data['lastName'];
    }

    /**
     * @param string $value
     */
    public function setLastName(string $value)
    {
        $this->data['lastName'] = $value;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->data['company'];
    }

    /**
     * @param string $value
     */
    public function setCompany(string $value)
    {
        $this->data['company'] = $value;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->data['Address1'];
    }

    /**
     * @param string $value
     */
    public function setAddress1(string $value)
    {
        $this->data['Address1'] = $value;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->data['Address2'];
    }

    /**
     * @param string $value
     */
    public function setAddress2(string $value)
    {
        $this->data['Address2'] = $value;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->data['city'];
    }

    /**
     * @param string $value
     */
    public function setCity(string $value)
    {
        $this->data['city'] = $value;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->data['state'];
    }

    /**
     * @param string $value
     */
    public function setState(string $value)
    {
        $this->data['state'] = $value;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->data['postcode'];
    }

    /**
     * @param string $value
     */
    public function setPostcode(string $value)
    {
        $this->data['postcode'] = $value;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->data['country'];
    }

    /**
     * @param string $value
     */
    public function setCountry(string $value)
    {
        $this->data['country'] = $value;
    }
}
