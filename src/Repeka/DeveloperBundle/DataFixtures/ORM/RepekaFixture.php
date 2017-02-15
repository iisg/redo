<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Repeka\Domain\Cqrs\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class RepekaFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {
    const ORDER = 0;

    /** @var ContainerInterface */
    protected $container;

    protected function handleCommand(Command $command, string $registerResultAs = '') {
        $result = $this->container->get('repeka.command_bus')->handle($command);
        if ($registerResultAs) {
            $this->addReference($registerResultAs, $result);
        }
        return $result;
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function getOrder() {
        return static::ORDER;
    }
}
