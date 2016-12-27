<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitialResourceKinds extends ContainerAwareFixture implements OrderedFixtureInterface {
    const ORDER = InitialMetadata::ORDER + 1;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $container */
        $container = $this->container;
        $container->get('repeka.command_bus')->handle(ResourceKindCreateCommand::fromArray([
            'label' => [
                'PL' => 'Książka',
                'EN' => 'Book',
            ],
            'metadataList' => [
                ['base_id' => 1],
                ['base_id' => 2],
                ['base_id' => 3],
                ['base_id' => 4],
                ['base_id' => 5],
            ],
        ]));
    }

    public function getOrder() {
        return self::ORDER;
    }
}
