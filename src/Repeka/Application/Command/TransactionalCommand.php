<?php

namespace Repeka\Application\Command;

use Repeka\Application\Repository\Transactional;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class TransactionalCommand extends Command {
    use Transactional;

    final protected function execute(InputInterface $input, OutputInterface $output) {
        $this->transactional(function () use ($input, $output) {
            return $this->executeInTransaction($input, $output);
        });
    }

    abstract protected function executeInTransaction(InputInterface $input, OutputInterface $output);
}
