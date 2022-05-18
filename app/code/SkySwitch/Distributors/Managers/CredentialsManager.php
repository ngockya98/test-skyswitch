<?php

namespace SkySwitch\Distributors\Managers;

class CredentialsManager
{
    /**
     * Return credentials detail
     *
     * @param mixed $distributor
     * @param mixed $deployment_config
     * @param mixed $customer
     * @return mixed
     */
    public function getCredentials($distributor, $deployment_config, $customer)
    {
        $skyswitch_settings = [];
        $magento_settings = $deployment_config->get('services/' . $distributor->getCode());

        if ($customer !== null && $customer->getExtensionAttributes()) {
            $skyswitch_settings = $customer->getExtensionAttributes()->getSkySwitchSettings()->getSettings();
        }

        if (isset($magento_settings['settings'])) {
            foreach ($magento_settings['settings'] as $key => $setting) {
                $magento_settings[$key] = isset($skyswitch_settings[$setting])
                    ? $skyswitch_settings[$setting]['value']
                    : $magento_settings[$key];
            }
            unset($magento_settings['settings']);
        }

        return $magento_settings;
    }
}
