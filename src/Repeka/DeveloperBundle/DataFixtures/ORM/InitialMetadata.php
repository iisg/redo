<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\DataModule\Bundle\Entity\Metadata;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;

class InitialMetadata extends ContainerAwareFixture {
    public function load(ObjectManager $manager) {
        $title = new Metadata();
        $title->setControl('text')
            ->setLabel([
                'PL' => 'Tytuł',
                'EN' => 'Title',
                'DE' => 'Titel',
            ])
            ->setDescription([
                'PL' => 'Tytuł zasobu',
                'EN' => 'The title of the resource',
                'DE' => 'Ressourcen Titel',
            ])
            ->setPlaceholder(([
                'PL' => 'Znajdziesz go na okładce',
                'EN' => 'Find it on the cover',
                'DE' => 'Sie finden es auf der Abdeckung',
            ]));
        $manager->persist($title);
        $manager->flush();
    }
}
