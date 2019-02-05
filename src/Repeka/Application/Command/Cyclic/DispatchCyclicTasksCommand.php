<?php
namespace Repeka\Application\Command\Cyclic;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchCyclicTasksCommand extends Command {
    /** @var CyclicCommand[]|iterable */
    private $cyclicCommands;

    public function __construct(iterable $cyclicCommands) {
        parent::__construct();
        $this->cyclicCommands = $cyclicCommands;
    }

    protected function configure() {
        $this
            ->setName('repeka:cyclic-tasks:dispatch')
            ->setDescription('Dispatches cyclic tasks. Should be executed every minute.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        FirewallMiddleware::bypass(
            function () use ($output) {
                $minuteInTheDay = intval(date('H')) * 60 + intval(date('i'));
                foreach ($this->cyclicCommands as $command) {
                    if ($minuteInTheDay % $command->getIntervalInMinutes() == 0) {
                        $this->getApplication()->run(new StringInput($command->getName()), $output);
                    }
                }
            }
        );
    }
}
