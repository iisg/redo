<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitialMetadata extends ContainerAwareFixture {
    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $container */
        $container = $this->container;
        $container->get('repeka.command_bus')->handle(MetadataCreateCommand::fromArray([
            'name' => 'Tytuł',
            'label' => [
                'PL' => 'Tytuł',
                'EN' => 'Title',
                'DE' => 'Titel',
            ],
            'description' => [
                'PL' => 'Tytuł zasobu',
                'EN' => 'The title of the resource',
                'DE' => 'Ressourcen Titel',
            ],
            'placeholder' => [
                'PL' => 'Znajdziesz go na okładce',
                'EN' => 'Find it on the cover',
                'DE' => 'Sie finden es auf der Abdeckung',
            ],
            'control' => 'text',
        ]));
    }
}
