<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class MetadataRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->metadataRepository = $this->container->get(MetadataRepository::class);
    }

    public function testFindAllByResourceClass() {
        $query = MetadataListQuery::builder()->filterByResourceClass('books')->onlyTopLevel()->build();
        $topLevelByResourceClass = $this->metadataRepository->findByQuery($query);
        $all = $this->metadataRepository->findAll();
        $allFiltered = array_values(
            array_filter(
                $all,
                function (Metadata $metadata) {
                    return $metadata->isTopLevel()
                        && ($metadata->getId() >= 0)
                        && ($metadata->getResourceClass() == 'books');
                }
            )
        );
        $allIds = EntityUtils::mapToIds($topLevelByResourceClass);
        $filteredIds = EntityUtils::mapToIds($allFiltered);
        sort($allIds);
        sort($filteredIds);
        $this->assertEquals($filteredIds, $allIds);
    }

    public function testFindByControlAndResourceClass() {
        $query = MetadataListQuery::builder()->filterByResourceClass('books')->filterByControl(MetadataControl::TEXT())->build();
        $textMetadata = $this->metadataRepository->findByQuery($query);
        $this->assertCount(9, $textMetadata);
    }

    public function testFindByIds() {
        $metadata1 = $this->findMetadataByName('Opis');
        $metadata2 = $this->findMetadataByName('Tytuł');
        $query = MetadataListQuery::builder()->filterByIds([$metadata1->getId(), $metadata2->getId()])->build();
        $metadata = $this->metadataRepository->findByQuery($query);
        $this->assertCount(2, $metadata);
    }

    public function testFindByUntrimmedName() {
        $this->assertEquals($this->metadataRepository->findByName('Opis'), $this->metadataRepository->findByName('Opis '));
    }

    public function testFindByNonDiacriticName() {
        $this->assertEquals($this->metadataRepository->findByName('Tytuł'), $this->metadataRepository->findByName('tytul'));
    }

    public function testRemoveResourceKindFromMetadataConstraints() {
        $name = 'Zobacz też';
        $rkId = 1;
        $resourceKindForRemoval = $this->createMock(ResourceKind::class);
        $resourceKindForRemoval->method('getId')->willReturn($rkId);
        $metadata = $this->metadataRepository->findByName($name);
        $this->assertContains($rkId, $metadata->getConstraints()['resourceKind']);
        $this->metadataRepository->removeResourceKindFromMetadataConstraints($resourceKindForRemoval);
        $this->resetEntityManager($this->metadataRepository);
        $afterRemovalMetadata = $this->metadataRepository->findByName($name);
        $this->assertNotContains($rkId, $afterRemovalMetadata->getConstraints()['resourceKind']);
    }

    public function testFilterByRequiredKindIds() {
        $kindId = 4;
        $requiredKindIds = [$kindId];
        $query = MetadataListQuery::builder()->filterByRequiredKindIds($requiredKindIds)->build();
        $allowedMetadataForResourceKind = $this->metadataRepository->findByQuery($query);
        $all = $this->metadataRepository->findAll();
        $allFiltered = array_values(
            array_filter(
                $all,
                function (Metadata $metadata) use ($kindId) {
                    return !array_key_exists('resourceKind', $metadata->getConstraints()) ||
                        in_array($kindId, $metadata->getConstraints()['resourceKind']);
                }
            )
        );
        $allIds = EntityUtils::mapToIds($allowedMetadataForResourceKind);
        $filteredIds = EntityUtils::mapToIds($allFiltered);
        sort($allIds);
        sort($filteredIds);
        $this->assertEquals($filteredIds, $allIds);
    }

    public function testAddingSystemMetadataToResult() {
        //filter by non existing metadata to get 0 results
        $query = MetadataListQuery::builder()->filterByIds([-1000])->addSystemMetadataIds(
            [SystemMetadata::USERNAME, SystemMetadata::PARENT, SystemMetadata::GROUP_MEMBER]
        )->build();
        $systemMetadataIds = array_map(
            function ($metadata) {
                return $metadata->getId();
            },
            $this->metadataRepository->findByQuery($query)
        );
        $this->assertContains(SystemMetadata::USERNAME, $systemMetadataIds);
        $this->assertContains(SystemMetadata::PARENT, $systemMetadataIds);
        $this->assertContains(SystemMetadata::GROUP_MEMBER, $systemMetadataIds);
    }

    public function testOrderingSystemMetadataByID() {
        $sortedIds = [SystemMetadata::USERNAME, SystemMetadata::PARENT, SystemMetadata::GROUP_MEMBER];
        sort($sortedIds);
        $unsortedIds = [$sortedIds[1], $sortedIds[0], $sortedIds[2]];
        // touch all metadata in not sorted order so postgres would return them in such order
        $this->metadataRepository->save((new SystemMetadata($sortedIds[1]))->toMetadata());
        $this->metadataRepository->save((new SystemMetadata($sortedIds[0]))->toMetadata());
        $this->metadataRepository->save((new SystemMetadata($sortedIds[2]))->toMetadata());
        $query = MetadataListQuery::builder()->filterByIds([-1000])->addSystemMetadataIds($unsortedIds)->build();
        $actualIds = array_map(
            function ($metadata) {
                return $metadata->getId();
            },
            $this->metadataRepository->findByQuery($query)
        );
        $this->assertEquals($sortedIds, $actualIds);
    }
}
