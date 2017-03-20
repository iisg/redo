<?php
namespace Repeka\Application\Command\Initialization;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface ApplicationInitializer {
    public function initialize(OutputInterface $output, ContainerInterface $container);
}
