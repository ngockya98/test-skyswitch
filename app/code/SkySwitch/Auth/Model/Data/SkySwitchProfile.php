<?php

namespace SkySwitch\Auth\Model\Data;

use SkySwitch\Auth\Api\Data\SkySwitchProfileInterface;

class SkySwitchProfile implements SkySwitchProfileInterface
{
    /**
     * @var string|null
     */
    protected string $account_id;

    /**
     * @var string|null
     */
    protected string $account_name;

    /**
     * @var string|null
     */
    protected string $account_number;

    /**
     * @var string|null
     */
    protected string $email;

    /**
     * @var array
     */
    protected array $permissions;

    /**
     * @param string|null $account_id
     * @param string|null $account_number
     * @param string|null $account_name
     * @param string|null $email
     * @param array $permissions
     */
    public function __construct(
        string $account_id = null,
        string $account_number = null,
        string $account_name = null,
        string $email = null,
        array  $permissions = []
    ) {
        $this->account_id = $account_id;
        $this->account_number = $account_number;
        $this->account_name = $account_name;
        $this->email = $email;
        $this->permissions = $permissions;
    }

    /**
     * Return static profile data array
     *
     * @param array $profile_data
     * @return static
     */
    public static function fromArray(array $profile_data) // phpcs:ignore
    {
        return new static(
            $profile_data['account_id'] ??  $profile_data['id'] ?? '',
            $profile_data['account_number'] ?? '',
            $profile_data['account_name'] ?? $profile_data['name'] ?? '',
            $profile_data['email'] ?? '',
            $profile_data['permissions'] ?? []
        );
    }

    /**
     * Return Account Id
     *
     * @return string|null
     */
    public function getAccountId(): ?string
    {
        return $this->account_id;
    }

    /**
     * Return Account name
     *
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->account_name;
    }

    /**
     * Return Account number
     *
     * @return string|null
     */
    public function getAccountNumber(): ?string
    {
        return $this->account_number;
    }

    /**
     * Return email
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Return permissions array
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
