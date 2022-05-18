<?php

namespace SkySwitch\Auth\Service;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Phrase;
use SkySwitch\Auth\Model\FusionAuthProfile;

class FusionAuth
{
    const BASE_URL = 'url'; // phpcs:ignore
    const API_KEY = 'key'; // phpcs:ignore
    const API_SECRET = 'secret'; // phpcs:ignore
    const APP_ID = 'app_id'; // phpcs:ignore
    const TENANT_ID = 'tenant_id'; // phpcs:ignore
    const REDIRECT_URL = 'redirect_url'; // phpcs:ignore

    /**
     * @var array
     */
    protected array $config;

    /**
     * @var string
     */
    protected string $access_token = '';

    /**
     * @var string
     */
    protected string $user_id;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->config = $deploymentConfig->get('services/fusionauth');
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
            self::API_KEY => $key,
            self::API_SECRET => $secret,
            self::APP_ID => $app_id,
            self::TENANT_ID => $tenant_id,
            self::REDIRECT_URL => $redirect_url
        ] = array_merge($config, [
            self::BASE_URL => 'https://localhost/',
            self::API_KEY => 'api_key_value',
            self::API_SECRET => 'api_secret_value',
            self::APP_ID => 'app_id_value',
            self::TENANT_ID => 'tenant_id_value',
            self::REDIRECT_URL => 'http://localhost/'
        ]);

        $this->config = compact(
            self::BASE_URL,
            self::API_KEY,
            self::API_SECRET,
            self::APP_ID,
            self::TENANT_ID,
            self::REDIRECT_URL
        );
    }

    /**
     * Return Redirect Url
     *
     * @return mixed
     */
    protected function redirectUrl()
    {
        return $this->config[self::REDIRECT_URL];
    }

    /**
     * Return Auth Url
     *
     * @return string
     */
    public function authUrl()
    {
        return $this->config[self::BASE_URL]
            .'oauth2/authorize?client_id=38f1690d-297e-43e4-9c09-7555e45daf75&response_type=code&redirect_uri='
            .$this->redirectUrl();
    }

    /**
     * Return Token Url
     *
     * @return string
     */
    public function tokenUrl()
    {
        return $this->config[self::BASE_URL] . 'oauth2/token/';
    }

    /**
     * Return User Id
     *
     * @return string
     */
    public function userId()
    {
        return $this->user_id;
    }

    /**
     * Return Fusion Auth Profile Class
     *
     * @return FusionAuthProfile
     */
    public function getProfile() : FusionAuthProfile
    {
        $curl = new Curl();

        $curl->setHeaders(['Authorization' => 'Bearer '. $this->access_token]);

        $path = $this->config[self::BASE_URL] . '/api/user/' . $this->user_id;

        $curl->get($path);

        return new FusionAuthProfile($curl->getBody());
    }

    /**
     * Set authenticate
     *
     * @param mixed $code
     * @return $this
     */
    public function authenticate($code)
    {
        if ($this->access_token) {
            return $this;
        };

        $curl = new Curl();

        $curl->post($this->tokenUrl(), [
            'client_id' => $this->config[self::APP_ID],
            'client_secret' => $this->config[self::API_SECRET],
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl()
        ]);

        $response = json_decode($curl->getBody(), true);

        if (array_key_exists('error', $response)) {
            throw new AuthenticationException(new Phrase('Problem authenticating: '. $response['error_description']));
        }

        $this->access_token = $response['access_token'] ?: null;
        $this->user_id = $response['userId'] ?: null;

        return $this;
    }
}
