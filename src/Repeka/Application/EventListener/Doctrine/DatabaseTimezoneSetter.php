<?php
namespace Repeka\Application\EventListener\Doctrine;

use Doctrine\DBAL\Event\ConnectionEventArgs;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DatabaseTimezoneSetter {
    use ContainerAwareTrait;

    private const TIMEZONE = 'Europe/Warsaw';

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function postConnect(ConnectionEventArgs $eventArgs) {
        $connection = $eventArgs->getConnection();
        $connection->exec(sprintf("SET TIMEZONE TO '%s'", self::TIMEZONE));
    }
}
