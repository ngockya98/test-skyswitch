<?php

namespace SkySwitch\Contracts\Traits;

trait LogRequest
{
    /**
     * Log method
     *
     * @param string $endpoint
     * @param string $method
     * @param array $request
     * @param array $response
     * @return void
     */
    public function log(string $endpoint, string $method, array $request, array $response)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/requests.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $line = [
            'service_name' => $this->getServiceName(),
            'method' => $method,
            'endpoint' => $endpoint,
            'request' => $request,
            'response' => $response
        ];

        $logger->info(json_encode($line));
    }
}
