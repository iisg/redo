<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitialLanguage extends ContainerAwareFixture {
    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $container */
        $container = $this->container;
        $container->get('repeka.command_bus')->handle(LanguageCreateCommand::fromArray([
            'code' => 'PL',
            'flag' => 'PL',
            'name' => 'polski',
        ]));
        $container->get('repeka.command_bus')->handle(LanguageCreateCommand::fromArray([
            'code' => 'EN',
            'flag' => 'GB',
            'name' => 'angielski',
        ]));
        $container->get('repeka.command_bus')->handle(LanguageCreateCommand::fromArray([
            'code' => 'GER',
            'flag' => 'DE',
            'name' => 'niemiecki',
        ]));
    }
}
