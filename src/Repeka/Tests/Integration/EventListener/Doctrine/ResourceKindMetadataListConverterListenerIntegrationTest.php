<?php
namespace Integration\EventListener\Doctrine;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Tests\IntegrationTestCase;

class ResourceKindMetadataListConverterListenerIntegrationTest extends IntegrationTestCase {
    /** @var ResourceKindRepository */
    private $metadataRepository;

    /** @before */
    public function init() {
        $this->metadataRepository = $this->container->get(ResourceKindRepository::class);
        $this->loadAllFixtures();
    }

    public function testSavingResourceKindWithMetadataList() {
        /** @var ResourceKind $resourceKind */
        $resourceKind = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'Nowy', 'EN' => 'New'],
                [$this->handleCommandBypassingFirewall(new MetadataGetQuery(2))]
            )
        );
        $this->assertCount(2, $resourceKind->getMetadataList());
        $this->assertInstanceOf(Metadata::class, $resourceKind->getMetadataList()[0]);
        $this->assertEquals(2, $resourceKind->getMetadataList()[0]->getId());
        $this->getEntityManager()->clear();
        $resourceKind = $this->handleCommandBypassingFirewall(new ResourceKindQuery($resourceKind->getId()));
        $this->assertCount(2, $resourceKind->getMetadataList());
        $this->assertInstanceOf(Metadata::class, $resourceKind->getMetadataList()[0]);
    }

    public function testSavingResourceKindWithMetadataOverrides() {
        /** @var ResourceKind $resourceKind */
        $resourceKind = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'Nowy', 'EN' => 'New'],
                [['id' => 2, 'label' => ['PL' => 'Nowa labelka w rodzaju']]]
            )
        );
        $this->assertCount(2, $resourceKind->getMetadataList());
        $this->assertInstanceOf(Metadata::class, $resourceKind->getMetadataList()[0]);
        $this->assertEquals(2, $resourceKind->getMetadataList()[0]->getId());
        $this->assertEquals('Nowa labelka w rodzaju', $resourceKind->getMetadataById(2)->getLabel()['PL']);
        $this->getEntityManager()->clear();
        $resourceKind = $this->handleCommandBypassingFirewall(new ResourceKindQuery($resourceKind->getId()));
        $this->assertCount(2, $resourceKind->getMetadataList());
        $overriddenMetadata = $resourceKind->getMetadataById(2);
        $this->assertInstanceOf(Metadata::class, $overriddenMetadata);
        $this->assertEquals('Nowa labelka w rodzaju', $overriddenMetadata->getLabel()['PL']);
    }

    public function testUpdatingResourceKindWithMetadataList() {
        /** @var ResourceKind $resourceKind */
        $resourceKind = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'Nowy', 'EN' => 'New'],
                [$this->handleCommandBypassingFirewall(new MetadataGetQuery(2))]
            )
        );
        $resourceKind = $this->handleCommandBypassingFirewall(
            new ResourceKindUpdateCommand(
                $resourceKind,
                $resourceKind->getLabel(),
                [
                    $this->handleCommandBypassingFirewall(new MetadataGetQuery(2)),
                    $this->handleCommandBypassingFirewall(new MetadataGetQuery(1)),
                ],
                $resourceKind->getDisplayStrategies()
            )
        );
        $this->assertCount(3, $resourceKind->getMetadataList());
        $this->assertInstanceOf(Metadata::class, $resourceKind->getMetadataList()[0]);
        $this->assertEquals(2, $resourceKind->getMetadataList()[0]->getId());
        $this->assertEquals(1, $resourceKind->getMetadataList()[1]->getId());
        $this->getEntityManager()->clear();
        $resourceKind = $this->handleCommandBypassingFirewall(new ResourceKindQuery($resourceKind->getId()));
        $this->assertCount(3, $resourceKind->getMetadataList());
        $this->assertInstanceOf(Metadata::class, $resourceKind->getMetadataList()[0]);
    }

    public function testMaintainingOrderOfMetadata() {
        /** @var ResourceKind $resourceKind */
        $resourceKind = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'Nowy', 'EN' => 'New'],
                [['id' => 2], ['id' => SystemMetadata::PARENT], ['id' => 1]]
            )
        );
        $this->assertCount(3, $resourceKind->getMetadataList());
        $this->assertEquals([2, SystemMetadata::PARENT, 1], $resourceKind->getMetadataIds());
        $this->getEntityManager()->clear();
        $resourceKind = $this->handleCommandBypassingFirewall(new ResourceKindQuery($resourceKind->getId()));
        $this->assertEquals([2, SystemMetadata::PARENT, 1], $resourceKind->getMetadataIds());
    }

    public function testOverridesForDifferentResourceKindsDoNotInfluenceEachOther() {
        /** @var ResourceKind $resourceKindA */
        $resourceKindA = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'A', 'EN' => 'A'],
                [['id' => 2, 'label' => ['PL' => 'Label A']]]
            )
        );
        /** @var ResourceKind $resourceKindB */
        $resourceKindB = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'B', 'EN' => 'B'],
                [['id' => 2, 'label' => ['PL' => 'Label B']]]
            )
        );
        $this->assertEquals('Label A', $resourceKindA->getMetadataById(2)->getLabel()['PL']);
        $this->assertEquals('Label B', $resourceKindB->getMetadataById(2)->getLabel()['PL']);
        $this->getEntityManager()->clear();
        $resourceKindA = $this->handleCommandBypassingFirewall(new ResourceKindQuery($resourceKindA->getId()));
        $resourceKindB = $this->handleCommandBypassingFirewall(new ResourceKindQuery($resourceKindB->getId()));
        $this->assertEquals('Label A', $resourceKindA->getMetadataById(2)->getLabel()['PL']);
        $this->assertEquals('Label B', $resourceKindB->getMetadataById(2)->getLabel()['PL']);
    }
}
