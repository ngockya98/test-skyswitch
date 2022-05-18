<?php
namespace Voip888\Service\Resources;

use Voip888\Service\Interfaces\Request888VoipInterface;

class Request888Voip implements Request888VoipInterface
{
    protected array $order;

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->order['orderNumber'];
    }

    /**
     * @param string $value
     */
    public function setOrderNumber(string $value)
    {
        $this->order['orderNumber'] = $value;
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->order['orderStatus'];
    }

    /**
     * @param string $value
     */
    public function setOrderStatus(string $value)
    {
        $this->order['orderStatus'] = $value;
    }

    /**
     * @return string
     */
    public function getOrderDate()
    {
        return $this->order['orderDate'];
    }

    /**
     * @param string $value
     */
    public function setOrderDate(string $value)
    {
        $this->order['orderDate'] = $value;
    }

    /**
     * @return string
     */
    public function getShippingTotal()
    {
        return $this->order['shippingTotal'];
    }

    /**
     * @param string $value
     */
    public function setShippingTotal(string $value)
    {
        $this->order['shippingTotal'] = $value;
    }

    /**
     * @return string
     */
    public function getTotal()
    {
        return $this->order['total'];
    }

    /**
     * @param string $value
     */
    public function setTotal(string $value)
    {
        $this->order['total'] = $value;
    }

    /**
     * @return string
     */
    public function getErpOrderNumber()
    {
        return $this->order['erpOrderNumber'];
    }

    /**
     * @param string $value
     */
    public function setErpOrderNumber(string $value)
    {
        $this->order['erpOrderNumber'] = $value;
    }

    /**
     * @return \Voip888\Service\Interfaces\Shipping888VoipInterface
     */
    public function getShipping()
    {
        return $this->order['shipping'];
    }

    /**
     * @param \Voip888\Service\Interfaces\Shipping888VoipInterface $value
     */
    public function setShipping($value)
    {
        $this->order['shipping'] = $value;
    }

    /**
     * @return \Voip888\Service\Interfaces\Shipping888VoipInterface
     */
    public function getBilling()
    {
        return $this->order['billing'];
    }

    /**
     * @param \Voip888\Service\Interfaces\Shipping888VoipInterface $value
     */
    public function setBilling($value)
    {
        $this->order['billing'] = $value;
    }

    /**
     * @return \Voip888\Service\Interfaces\Item888VoipInterface[]
     */
    public function getItems()
    {
        return $this->order['items'];

    }

    /**
     * @param \Voip888\Service\Interfaces\Item888VoipInterface[] $value
     */
    public function setItems($value)
    {
        $this->order['items'] = $value;
    }

    /**
     * @return \Voip888\Service\Interfaces\Tracking888VoipInterface[]
     */
    public function getTracking(){
        return $this->order['tracking'] ?? [];
    }

    /**
     * @param \Voip888\Service\Interfaces\Tracking888VoipInterface[] $value
     */
    public function setTracking($value)
    {
        $this->order['tracking'] = $value;
    }

    /**
     * @return \Voip888\Service\Interfaces\Provisioning888VoipInterface[]
     */
    public function getProvisioning(){
        return $this->order['provisioning'];
    }

    /**
     * @param \Voip888\Service\Interfaces\Provisioning888VoipInterface[] $value
     */
    public function setProvisioning($value)
    {
        $this->order['provisioning'] = $value;
    }

}
