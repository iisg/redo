<?php
namespace Repeka\Application\EventListener;

use M6Web\Component\Statsd\Client;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class AdminPanelMetricsRequestListener {
    /** @var Client */
    private $statsd;

    /**
     * @var array
     */
    private $namesWhiteList;

    public function __construct(Client $statsd, array $namesWhitelist) {
        $this->statsd = $statsd;
        $this->initializeNamesWhitelist($namesWhitelist);
    }

    private function initializeNamesWhitelist($namesWhitelist) {
        $this->namesWhiteList = array_map(function ($whitelistedName) {
            if (substr($whitelistedName, -1) == '*') {
                $baseName = substr($whitelistedName, 0, -1);
                return function ($nameToCheck) use ($baseName) {
                    return strpos($nameToCheck, $baseName) === 0;
                };
            } else {
                return function ($nameToCheck) use ($whitelistedName) {
                    return $nameToCheck === $whitelistedName;
                };
            }
        }, $namesWhitelist);
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        if ($request->headers->has('X-Metrics')) {
            $metricsString = base64_decode($request->headers->get('X-Metrics'));
            if ($metricsString) {
                $metrics = json_decode($metricsString, true);
                if ($metrics) {
                    $this->storeMetrics($metrics);
                }
            }
        }
    }

    public function storeMetrics(array $metrics) {
        $filteredMetrics = array_filter($metrics, [$this, 'isNameWhitelisted']);
        if (count($filteredMetrics) < count($metrics)) {
            $filteredMetrics[] = ['type' => 'c', 'name' => 'invalid_metrics', 'value' => count($metrics) - count($filteredMetrics)];
        }
        foreach ($filteredMetrics as $metric) {
            $metric['name'] = 'repeka.admin_panel.' . $metric['name'];
            switch ($metric['type']) {
                case 'c':
                    $this->storeCounter($metric);
                    break;
                case 't':
                    $this->storeTimer($metric);
                    break;
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) it is used as the array notation callback in #storeMetrics
     */
    private function isNameWhitelisted($metric) {
        foreach ($this->namesWhiteList as $allowedNameChecker) {
            if ($allowedNameChecker($metric['name'])) {
                return true;
            }
        }
        return false;
    }

    private function storeCounter($metric) {
        $this->statsd->count($metric['name'], $metric['value']);
    }

    private function storeTimer($metric) {
        $this->statsd->timing($metric['name'], $metric['value']);
    }
}
