<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

use Amasty\CompanyAccount\Controller\Router;

class UrlModifier
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function modify(string $url): string
    {
        return str_replace(Router::AMASTY_COMPANY_ROUTE, $this->configProvider->getUrlKey(), $url);
    }
}
