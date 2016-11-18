<?php
namespace Repeka\Application\Cqrs\Middleware;

use M6Web\Bundle\StatsdBundle\Client\Client;
use Repeka\Domain\Cqrs\Command;

class MetricsCommandBusMiddleware implements CommandBusMiddleware {
    /**
     * @var Client
     */
    private $statsd;

    public function __construct(Client $statsd) {
        $this->statsd = $statsd;
    }

    public function handle(Command $command, callable $next) {
        $useCaseName = 'repeka.use_case.' . $command->getCommandName();
        $startMemory = memory_get_usage();
        $startTime = microtime(true);
        try {
            $result = $next($command);
            $elapsedTime = round((microtime(true) - $startTime) * 1000);
            $usedMemory = memory_get_usage() - $startMemory;
            $this->statsd->increment($useCaseName . '.success');
            $this->statsd->timing($useCaseName . '.memory', $usedMemory);
            $this->statsd->timing($useCaseName . '.time', $elapsedTime);
            return $result;
        } catch (\Exception $e) {
            $this->statsd->increment($useCaseName . '.failure');
            throw $e;
        }
    }
}
