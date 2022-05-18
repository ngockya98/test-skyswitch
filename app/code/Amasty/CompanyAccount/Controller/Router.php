<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Controller;

use Amasty\CompanyAccount\Model\ConfigProvider;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;

class Router implements \Magento\Framework\App\RouterInterface
{
    public const AMASTY_COMPANY_ROUTE = 'amasty_company';
    public const DEFAULT_ACTION = 'index';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    public function __construct(
        ConfigProvider $configProvider,
        ActionFactory $actionFactory
    ) {
        $this->configProvider = $configProvider;
        $this->actionFactory = $actionFactory;
    }

    public function match(RequestInterface $request)
    {
        $urlKey = $this->configProvider->getUrlKey();
        if (!$urlKey) {
            return false;
        }

        $identifier = $request->getPathInfo();
        $pathData = explode('/', trim($identifier, '/'));

        if (isset($pathData[0]) && isset($pathData[1]) && $pathData[0] === $urlKey) {
            $request->setModuleName(self::AMASTY_COMPANY_ROUTE)
                ->setControllerName($pathData[1])
                ->setActionName($pathData[2] ?? self::DEFAULT_ACTION);
        }

        return false;
    }
}
