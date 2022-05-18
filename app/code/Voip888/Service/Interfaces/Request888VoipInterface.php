<?php

namespace Voip888\Service\Interfaces;

interface Request888VoipInterface
{
    public function getOrder();

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
    public function getOrderStatus();

    /**
     * @param string $value
     */
    public function setOrderStatus(string $value);

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
    public function getShippingTotal();

    /**
     * @param string $value
     */
    public function setShippingTotal(string $value);

    /**
     * @return string
     */
    public function getTotal();

    /**
     * @param string $value
     */
    public function setTotal(string $value);

    /**
     * @return string
     */
    public function getErpOrderNumber();

    /**
     * @param string $value
     */
    public function setErpOrderNumber(string $value);

    /**
     * @return \Voip888\Service\Interfaces\Shipping888VoipInterface
     */
    public function getShipping();

    /**
     * @param \Voip888\Service\Interfaces\Shipping888VoipInterface $value
     */
    public function setShipping($value);

    /**
     * @return \Voip888\Service\Interfaces\Shipping888VoipInterface
     */
    public function getBilling();

    /**
     * @param \Voip888\Service\Interfaces\Shipping888VoipInterface $value
     */
    public function setBilling($value);

    /**
     * @return \Voip888\Service\Interfaces\Item888VoipInterface[]
     */
    public function getItems();

    /**
     * @param \Voip888\Service\Interfaces\Item888VoipInterface[] $value
     */
    public function setItems($value);

    /**
     * @return \Voip888\Service\Interfaces\Tracking888VoipInterface[]
     */
    public function getTracking();

    /**
     * @param \Voip888\Service\Interfaces\Tracking888VoipInterface[] $value
     */
    public function setTracking($value);

    /**
     * @return \Voip888\Service\Interfaces\Provisioning888VoipInterface[]
     */
    public function getProvisioning();

    /**
     * @param \Voip888\Service\Interfaces\Provisioning888VoipInterface[] $value
     */
    public function setProvisioning($value);
    
}
