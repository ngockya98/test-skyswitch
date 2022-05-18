<?php

namespace SkySwitch\Auth\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\HTTP\Client\Curl;
use SkySwitch\Auth\Api\Data\SkySwitchProfileInterface;
use SkySwitch\Auth\Api\Data\SkySwitchServiceInterface;
use SkySwitch\Auth\Api\Data\SkySwitchSettingsInterface;
use SkySwitch\Auth\Model\Data\SkySwitchProfile;
use SkySwitch\Auth\Model\Data\SkySwitchSettings;

class SkySwitchService implements SkySwitchServiceInterface
{
    const BASE_URL = 'endpoint'; //phpcs:ignore
    const CLIENT_ID = 'client_id'; //phpcs:ignore
    const CLIENT_SECRET = 'client_secret'; //phpcs:ignore
    const USERNAME = 'username'; //phpcs:ignore
    const PASSWORD = 'password'; //phpcs:ignore

    /**
     * @var null
     */
    protected $access_token = null;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->config = $deploymentConfig->get('services/skyswitch');
    }

    /**
     * Set config value
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config = [])
    {
        [
            self::BASE_URL => $url,
            self::CLIENT_ID => $client_id,
            self::CLIENT_SECRET => $client_secret,
            self::USERNAME => $username,
            self::PASSWORD => $password,
        ] = array_merge($config, [
            self::BASE_URL => 'https://localhost/',
            self::CLIENT_ID => 'client_id_value',
            self::CLIENT_SECRET => 'client_secret_value',
            self::USERNAME => 'username_value',
            self::PASSWORD => 'password_value',
        ]);

        $this->config = compact(
            self::BASE_URL,
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::USERNAME,
            self::PASSWORD
        );
    }

    /**
     * Return base url with path
     *
     * @param string $path
     * @return string
     */
    protected function baseUrl($path)
    {
        return rtrim($this->config[self::BASE_URL], '/') . '/' . trim($path, '/');
    }

    /**
     * Set authenticate
     *
     * @return mixed|null
     */
    protected function authenticate()
    {
        if ($this->access_token) {
            return $this->access_token;
        }

        $curl = new Curl();
        $curl->addHeader('Content-Type', 'application/json');
        $curl->post($this->baseUrl('oauth/token'), json_encode([
            'grant_type' => 'password',
            'client_id' => $this->config[self::CLIENT_ID],
            'client_secret' => $this->config[self::CLIENT_SECRET],
            'scope' => '*',
            'username' => $this->config[self::USERNAME],
            'password' => $this->config[self::PASSWORD],
        ]));

        $response = json_decode($curl->getBody(), true);

        return $this->access_token = $response['access_token'];
    }

    /**
     * Return SkySwitchProfile Model
     *
     * @param int $reseller_id
     * @return SkySwitchProfileInterface
     * @throws \Exception
     */
    public function getProfile(int $reseller_id) : SkySwitchProfileInterface
    {
        $curl = $this->newRequest();

        $curl->get($this->baseUrl('profile'), []);

        $response = json_decode($curl->getBody(), true);

        $super_user_profile = SkySwitchProfile::fromArray($response);

        $super_user_listed_sub_accounts = $this->listSubAccounts($super_user_profile->getAccountId(), [
            'account_number'=>$reseller_id
        ]);

        if (!count($super_user_listed_sub_accounts)
            && !isset($super_user_listed_sub_accounts[0])
            && !isset($super_user_listed_sub_accounts[0]['children'])
            && !count($super_user_listed_sub_accounts[0]['children'])
        ) {
            throw new \ErrorException('Skyswitch reseller profile not found!');
        }

        $profile = $super_user_listed_sub_accounts[0]['children'][0];

        return SkySwitchProfile::fromArray($profile);
    }

    /**
     * Return new request with authenticate and authorization
     *
     * @return Curl
     */
    public function newRequest()
    {
        $this->authenticate();

        $curl = new Curl();

        $curl->setHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ]);

        return $curl;
    }

    /**
     * Return list sub accounts
     *
     * @param int|string $account_id
     * @param array $options
     * @return mixed
     */
    public function listSubAccounts($account_id, array $options = [])
    {
        $recursive          = $options['recursive'] ?? 1;
        $include_lineage    = $options['include_lineage'] ?? 1;
        $account_number     = $options['account_number'] ?? '';

        $request = $this->newRequest();

        $path = sprintf(
            'accounts/%s/sub-accounts?%s',
            $account_id,
            http_build_query(compact(
                'recursive',
                'include_lineage',
                'account_number'
            ))
        );

        $request->get($this->baseUrl($path));

        return json_decode($request->getBody(), true);
    }

    /**
     * Return SkySwitchSettings Class
     *
     * @param int|string $account_id
     * @return SkySwitchSettingsInterface
     */
    public function getSettings($account_id): SkySwitchSettingsInterface
    {
        $curl = $this->newRequest();

        $curl->get(sprintf(
            $this->baseUrl('accounts/%s/settings'),
            $account_id
        ));

        return new SkySwitchSettings(json_decode($curl->getBody(), true) ?? []);
    }
}
