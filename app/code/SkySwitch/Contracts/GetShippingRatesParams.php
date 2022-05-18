<?php

namespace SkySwitch\Contracts;

class GetShippingRatesParams
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected $shipping_address;

    /**
     * @var array
     */
    protected $shipping_contact;

    /**
     * @var string
     */
    protected $po_number = '';

    /**
     * @var bool
     */
    protected $provision = false;

    /**
     * @param array $shipping_address
     * @param array $items
     * @param array $shipping_contact
     */
    public function __construct(array $shipping_address = [], array $items = [], array $shipping_contact = [])
    {
        $this->shipping_address = $shipping_address;
        $this->items = $items;
        $this->shipping_contact = $shipping_contact;
    }

    /**
     * Set Shipping Address
     *
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $state
     * @param string $country
     * @param string $zip
     * @param string $phone
     * @param string $address_type
     * @return void
     */
    public function setShippingAddress($address1, $address2, $city, $state, $country, $zip, $phone, $address_type = '')
    {
        $this->shipping_address['address1'] = $address1;
        $this->shipping_address['address2'] = $address2;
        $this->shipping_address['city'] = $city;
        $this->shipping_address['state'] = $state;
        $this->shipping_address['country'] = $country;
        $this->shipping_address['zip'] = $zip;
        $this->shipping_address['phone'] = $phone;
        $this->shipping_address['address_type'] = $address_type;
    }

    /**
     * Set contact
     *
     * @param string $name
     * @param string $phone
     * @param string $email
     * @return void
     */
    public function setContact($name, $phone, $email = '')
    {
        $this->shipping_contact['name'] = $name;
        $this->shipping_contact['phone'] = $phone;
        $this->shipping_contact['email'] = $email;
    }

    /**
     * Set Po Number
     *
     * @param string $po_number
     * @return void
     */
    public function setPoNumber(string $po_number)
    {
        $this->po_number = $po_number;
    }

    /**
     * Set provision
     *
     * @param bool $provision
     * @return void
     */
    public function setProvision(bool $provision)
    {
        $this->provision = $provision;
    }

    /**
     * Add new item
     *
     * @param string $sku
     * @param int|string $qty
     * @return void
     */
    public function addItem($sku, $qty)
    {
        $this->items[] = [
            'sku' => $sku,
            'qty' => $qty
        ];
    }

    /**
     * Return shipping address
     *
     * @return array
     */
    public function getShippingAddress(): array
    {
        return $this->shipping_address;
    }

    /**
     * Return shipping items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Return shipping contact
     *
     * @return array
     */
    public function getContact(): array
    {
        return $this->shipping_contact;
    }

    /**
     * Return Po number
     *
     * @return string
     */
    public function getPoNumber(): string
    {
        return $this->po_number;
    }

    /**
     * Return provision
     *
     * @return bool
     */
    public function getProvision(): bool
    {
        return $this->provision;
    }
}
