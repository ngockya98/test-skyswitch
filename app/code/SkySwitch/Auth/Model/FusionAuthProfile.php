<?php

namespace SkySwitch\Auth\Model;

class FusionAuthProfile
{
    /**
     * @var string|mixed
     */
    protected string $full_name;

    /**
     * @var string|mixed
     */
    protected string $id;

    /**
     * @var string
     */
    protected string $mobile_phone;

    /**
     * @var string|mixed
     */
    protected string $email;

    /**
     * @var int|mixed
     */
    protected int $reseller_id;

    /**
     * @param string $profileResponse
     */
    public function __construct(string $profileResponse)
    {
        try {
            $response = json_decode($profileResponse, true)['user'];
        } catch (\Exception $e) {
            $response = [];
        }

        $this->full_name = $response['fullName'] ?? '';
        $this->email = $response['email'];
        $this->id = $response['id'];
        $this->reseller_id = $response['data']['reseller_id'];
    }

    /**
     * Return full name
     *
     * @return mixed|string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Return first name
     *
     * @return mixed|string
     */
    public function getFirstName()
    {
        return explode(' ', $this->getFullName())[0];
    }

    /**
     * Return last name
     *
     * @return mixed|string
     */
    public function getLastName()
    {
        return explode(' ', $this->getFullName())[1];
    }

    /**
     * Return Id
     *
     * @return mixed|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return mobile phone
     *
     * @return string
     */
    public function getMobilePhone(): string
    {
        return $this->mobile_phone;
    }

    /**
     * Return email
     *
     * @return mixed|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return reseller id
     *
     * @return int|mixed
     */
    public function getResellerId()
    {
        return $this->reseller_id;
    }
}
