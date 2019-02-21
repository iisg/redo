<?php
namespace Repeka\Application\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouteCollection;

class PluginsRoutesLoader {
    /** @var AnnotatedRouteControllerLoader */
    private $routeLoader;

    public function __construct(AnnotatedRouteControllerLoader $routeLoader) {
        $this->routeLoader = $routeLoader;
    }

    public function loadRoutes(): RouteCollection {
        $collection = new RouteCollection();
        foreach ($this->getPluginsControllersClassNames() as $controllersClassName) {
            $newCollection = $this->routeLoader->load($controllersClassName);
            $collection->addCollection($newCollection);
        }
        return $collection;
    }

    private function getPluginsControllersClassNames(): array {
        $pluginControllers = $this->getPluginControllers();
        $classNames = [];
        foreach ($pluginControllers as $pluginController) {
            /** @var \SplFileInfo $pluginController */
            $path = $pluginController->getRealPath();
            $controllerClassName = substr(basename($path), 0, -4); // cut .php
            $namespace = basename(dirname(dirname($path)));
            $fullClassName = 'Repeka\\Plugins\\' . $namespace . '\\Controller\\' . $controllerClassName;
            $classNames[] = $fullClassName;
        }
        return $classNames;
    }

    /** @return array|Finder */
    private function getPluginControllers() {
        try {
            $finder = new Finder();
            $finder->files()
                ->in(\AppKernel::APP_PATH . '/../src/Repeka/Plugins/*/Controller/')
                ->name('*Controller.php')
                ->depth('==0');
        } catch (\InvalidArgumentException $e) {
            $finder = [];
        }
        return $finder;
    }
}
