<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Test\Unit\Controller;

use Amasty\CompanyAccount\Test\Unit\Traits;
use Amasty\CompanyAccount\Controller\Router;
use Amasty\CompanyAccount\Model\ConfigProvider;
use Magento\Framework\App\Request\Http;

/**
 * Class RouterTest
 *
 * @see Router
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Router::match
     * @dataProvider getMatchDataProvider
     */
    public function testMatch(string $urlKey, string $pathinfo, bool $result)
    {
        $configProvider = $this->createMock(ConfigProvider::class);
        $controller = $this->getObjectManager()->getObject(
            Router::class,
            ['configProvider' => $configProvider]
        );

        $request = $this->createMock(Http::class);
        $configProvider->expects($this->any())->method('getUrlKey')->willReturn($urlKey);
        $request->expects($this->any())->method('getPathInfo')->willReturn($pathinfo);

        if (!$result) {
            $this->assertFalse($controller->match($request));
        } else {
            $request->expects($this->once())->method('setModuleName')
                ->with(Router::AMASTY_COMPANY_ROUTE)
                ->willReturn($request);
            $request->expects($this->once())->method('setControllerName')->with('path')->willReturn($request);
            $request->expects($this->once())->method('setActionName')->with('index');
            $controller->match($request);
        }
    }

    /**
     * Data provider for match test
     * @return array
     */
    public function getMatchDataProvider()
    {
        return [
            ['', '', false],
            ['test', 'path', false],
            ['test', '/test/path/index/', true],
        ];
    }
}
