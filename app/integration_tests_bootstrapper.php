<?php
$originalBootstrapResult = require_once 'autoload.php';
require_once __DIR__ . '/AppKernel.php';
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;

function executeCommand(Application $application, string $command) {
    $input = new StringInput("$command --quiet --env=test");
    $input->setInteractive(false);
    $application->run($input);
}

try {
    echo "Bootstrapping integration tests...\n";
    /** @noinspection PhpUndefinedClassInspection */
    $kernel = new AppKernel('test', true);
    $kernel->boot();
    $application = new Application($kernel);
    $application->setAutoExit(false);
    executeCommand($application, 'doctrine:database:drop --force --if-exists');
    executeCommand($application, 'doctrine:database:create');
    executeCommand($application, 'doctrine:migrations:migrate');
    define('INTEGRATION_TESTS_BOOTSTRAPPED', true);
} finally {
    if ($kernel) {
        $kernel->shutdown();
    }
}
return $originalBootstrapResult;
