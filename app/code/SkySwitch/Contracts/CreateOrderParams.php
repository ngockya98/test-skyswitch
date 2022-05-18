<?php

namespace SkySwitch\Contracts;

use FG\ASN1\Universal\Boolean;

class CreateOrderParams
{
    const RESIDENTIAL = 'Residential'; //phpcs:ignore
    const COMERCIAL = 'Comercial'; //phpcs:ignore

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
    protected $billing_address;

    /**
     * @var mixed|string
     */
    protected $po_number;

    /**
     * @var mixed|string
     */
    protected $shipping_method;

    /**
     * @var mixed|string
     */
    protected $shipping_carrier;

    /**
     * @var array
     */
    protected $contact;

    /**
     * @var mixed|string
     */
    protected $quote_number;

    /**
     * @var false|mixed
     */
    protected $provision;

    /**
     * @param array $shipping_address
     * @param array $items
     * @param array $billing_address
     * @param array $contact
     * @param number|string $po_number
     * @param string $shipping_method
     * @param number|string $quote_number
     * @param string $shipping_carrier
     * @param bool $provision
     */
    public function __construct(
        array $shipping_address = [],
        array $items = [],
        array $billing_address = [],
        array $contact = [],
        $po_number = '',
        $shipping_method = '',
        $quote_number = '',
        $shipping_carrier = '',
        $provision = false
    ) {
        $this->shipping_address = $shipping_address;
        $this->items = $items;
        $this->billing_address = $billing_address;
        $this->po_number = $po_number;
        $this->shipping_method = $shipping_method;
        $this->contact = $contact;
        $this->quote_number = $quote_number;
        $this->shipping_carrier = $shipping_carrier;
        $this->provision = $provision;
    }

    /**
     * Set shipping address value
     *
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $state
     * @param string $country
     * @param mixed $zip
     * @param mixed $phone
     * @param mixed $address_type
     * @return void
     */
    public function setShippingAddress(
        $address1,
        $address2,
        $city,
        $state,
        $country,
        $zip,
        $phone,
        $address_type = self::RESIDENTIAL
    ) {
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
     * Set billing address
     *
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $state
     * @param string $country
     * @param mixed $zip
     * @param mixed $phone
     * @param mixed $address_type
     * @return void
     */
    public function setBillingAddress($address1, $address2, $city, $state, $country, $zip, $phone, $address_type = '')
    {
        $this->billing_address['address1'] = $address1;
        $this->billing_address['address2'] = $address2;
        $this->billing_address['city'] = $city;
        $this->billing_address['state'] = $state;
        $this->billing_address['country'] = $country;
        $this->billing_address['zip'] = $zip;
        $this->billing_address['phone'] = $phone;
        $this->billing_address['address_type'] = $address_type;
    }

    /**
     * Set contact
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $company
     * @param string $email
     * @return void
     */
    public function setContact($first_name, $last_name, $company = '', $email = '')
    {
        $this->contact['company'] = $company;
        $this->contact['first_name'] = $first_name;
        $this->contact['last_name'] = $last_name;
        $this->contact['email'] = $email;
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
     * Set quote number
     *
     * @param string $quote_number
     * @return void
     */
    public function setQuoteNumber(string $quote_number)
    {
        $this->quote_number = $quote_number;
    }

    /**
     * Set shipping method
     *
     * @param string $shipping_method
     * @return void
     */
    public function setShippingMethod(string $shipping_method)
    {
        $this->shipping_method = $shipping_method;
    }

    /**
     * Set shipping carrier
     *
     * @param string $shipping_carrier
     * @return void
     */
    public function setShippingCarrier(string $shipping_carrier)
    {
        $this->shipping_carrier = $shipping_carrier;
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
     * Add new shipping item
     *
     * @param string $sku
     * @param number|string $qty
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
     * Return billing address
     *
     * @return array
     */
    public function getBillingAddress(): array
    {
        return $this->billing_address;
    }

    /**
     * Return contact
     *
     * @return array
     */
    public function getContact(): array
    {
        return $this->contact;
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
     * Return quote number
     *
     * @return string
     */
    public function getQuoteNumber(): string
    {
        return $this->quote_number;
    }

    /**
     * Return shipping method
     *
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->shipping_method;
    }

    /**
     * Return shipping carrier
     *
     * @return string
     */
    public function getShippingCarrier(): string
    {
        return $this->shipping_carrier;
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
