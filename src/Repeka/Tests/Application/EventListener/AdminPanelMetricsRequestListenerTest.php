<?php
namespace Repeka\Tests\Application\EventListener;

use M6Web\Bundle\StatsdBundle\Client\Client;
use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\EventListener\AdminPanelMetricsRequestListener;

class AdminPanelMetricsRequestListenerTest extends \PHPUnit_Framework_TestCase {
    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $statsd;

    /** @var  AdminPanelMetricsRequestListener */
    private $listener;

    protected function setUp() {
        $this->statsd = $this->getMockBuilder(Client::class)
            ->setConstructorArgs([[]])
            ->setMethods(['count', 'timing', 'init'])
            ->disableProxyingToOriginalMethods()
            ->getMock();
    }

    public function testStoringCountMetric() {
        $this->listener = new AdminPanelMetricsRequestListener($this->statsd, ['valid_event']);
        $metric = ['type' => 'c', 'value' => 2, 'name' => 'valid_event'];
        $this->statsd->expects($this->once())
            ->method('count')
            ->with($this->stringContains($metric['name']), $this->equalTo($metric['value']));
        $this->listener->storeMetrics([$metric]);
    }

    public function testStoringTimeMetric() {
        $this->listener = new AdminPanelMetricsRequestListener($this->statsd, ['valid_event']);
        $metric = ['type' => 't', 'value' => 2, 'name' => 'valid_event'];
        $this->statsd->expects($this->once())
            ->method('timing')
            ->with($this->stringContains($metric['name']), $this->equalTo($metric['value']));
        $this->listener->storeMetrics([$metric]);
    }

    public function testPreventingToStoreNotWhitelistedMetric() {
        $this->listener = new AdminPanelMetricsRequestListener($this->statsd, ['valid_event']);
        $metric = ['type' => 'c', 'value' => 2, 'name' => 'invalid_event'];
        $this->statsd->expects($this->once())
            ->method('count')
            ->with($this->stringContains('invalid_metrics'), $this->equalTo(1));
        $this->listener->storeMetrics([$metric]);
    }

    public function testAllowingToStoreWildcardWhitelistedMetric() {
        $this->listener = new AdminPanelMetricsRequestListener($this->statsd, ['valid_events.*']);
        $metric = ['type' => 'c', 'value' => 2, 'name' => 'valid_events.something'];
        $this->statsd->expects($this->once())
            ->method('count')
            ->with($this->stringContains($metric['name']), $this->equalTo($metric['value']));
        $this->listener->storeMetrics([$metric]);
    }
}
