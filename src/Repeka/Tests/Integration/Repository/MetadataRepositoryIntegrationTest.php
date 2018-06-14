<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class MetadataRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function setUp() {
        parent::setUp();
        $this->metadataRepository = $this->container->get(MetadataRepository::class);
        $this->loadAllFixtures();
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
        $this->assertCount(4, $textMetadata);
    }

    public function testFindByIds() {
        $metadata1 = $this->findMetadataByName('Opis');
        $metadata2 = $this->findMetadataByName('Tytuł');
        $query = MetadataListQuery::builder()->filterByIds([$metadata1->getId(), $metadata2->getId()])->build();
        $metadata = $this->metadataRepository->findByQuery($query);
        $this->assertCount(2, $metadata);
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
}
