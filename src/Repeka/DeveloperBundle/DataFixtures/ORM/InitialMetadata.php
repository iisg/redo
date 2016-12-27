<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InitialMetadata extends ContainerAwareFixture implements OrderedFixtureInterface {
    const ORDER = InitialLanguage::ORDER + 1;

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
            ],
            'description' => [
                'PL' => 'Tytuł zasobu',
                'EN' => 'The title of the resource',
            ],
            'placeholder' => [
                'PL' => 'Znajdziesz go na okładce',
                'EN' => 'Find it on the cover',
            ],
            'control' => 'text',
        ]));
        $container->get('repeka.command_bus')->handle(MetadataCreateCommand::fromArray([
            'name' => 'Opis',
            'label' => [
                'PL' => 'Opis',
                'EN' => 'Description',
            ],
            'description' => [
                'PL' => 'Napisz coś więcej',
                'EN' => 'Tell me more',
            ],
            'placeholder' => [],
            'control' => 'textarea',
        ]));
        $container->get('repeka.command_bus')->handle(MetadataCreateCommand::fromArray([
            'name' => 'Data wydania',
            'label' => [
                'PL' => 'Data wydania',
                'EN' => 'Publish date',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'date',
        ]));
        $container->get('repeka.command_bus')->handle(MetadataCreateCommand::fromArray([
            'name' => 'Czy ma twardą okładkę?',
            'label' => [
                'PL' => 'Twarda okładka',
                'EN' => 'Hard cover',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'boolean',
        ]));
        $container->get('repeka.command_bus')->handle(MetadataCreateCommand::fromArray([
            'name' => 'Liczba stron',
            'label' => [
                'PL' => 'Liczba stron',
                'EN' => 'Number of pages',
            ],
            'description' => [],
            'placeholder' => [],
            'control' => 'integer',
        ]));
    }

    public function getOrder() {
        return self::ORDER;
    }
}
