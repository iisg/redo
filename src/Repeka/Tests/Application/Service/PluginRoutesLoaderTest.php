<?php
namespace Repeka\Tests\Application\Service;

use Repeka\Application\Service\PluginsRoutesLoader;
use Repeka\Plugins\Cmi\Controller\CmiHomeController;
use Repeka\Plugins\Redo\Controller\RedoFtsSearchController;
use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Routing\RouteCollection;

class PluginRoutesLoaderTest extends \PHPUnit_Framework_TestCase {
    private $loadedControllers = [];
    /** @var AnnotatedRouteControllerLoader|\PHPUnit_Framework_MockObject_MockObject */
    private $routeLoader;

    /** @before */
    public function init() {
        $this->routeLoader = $this->createMock(AnnotatedRouteControllerLoader::class);
        $this->routeLoader->method('load')->willReturnCallback(
            function ($controllerClassName) {
                $this->loadedControllers[] = $controllerClassName;
                return new RouteCollection();
            }
        );
    }

    public function testNoSortingWhenNoTheme() {
        $loader = new PluginsRoutesLoader('', $this->routeLoader);
        $loader->loadRoutes();
        $this->assertContains(CmiHomeController::class, $this->loadedControllers);
        $this->assertContains(RedoFtsSearchController::class, $this->loadedControllers);
        $this->assertLessThan(
            array_search(RedoFtsSearchController::class, $this->loadedControllers),
            array_search(CmiHomeController::class, $this->loadedControllers)
        );
    }

    public function testLoadsCmiControllersFirstIfCmiTheme() {
        $loader = new PluginsRoutesLoader('cmi', $this->routeLoader);
        $loader->loadRoutes();
        $this->assertLessThan(
            array_search(RedoFtsSearchController::class, $this->loadedControllers),
            array_search(CmiHomeController::class, $this->loadedControllers)
        );
    }

    public function testLoadsRedoControllersFirstIfRedoTheme() {
        $loader = new PluginsRoutesLoader('redo', $this->routeLoader);
        $loader->loadRoutes();
        $this->assertLessThan(
            array_search(CmiHomeController::class, $this->loadedControllers),
            array_search(RedoFtsSearchController::class, $this->loadedControllers)
        );
    }

    public function testWorksWhenInvalidTheme() {
        $loader = new PluginsRoutesLoader('unicorn', $this->routeLoader);
        $loader->loadRoutes();
        $this->assertContains(CmiHomeController::class, $this->loadedControllers);
        $this->assertContains(RedoFtsSearchController::class, $this->loadedControllers);
    }
}
