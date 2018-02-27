<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Tests\IntegrationTestCase;

class MetadataRepositoryIntegrationTest extends IntegrationTestCase {
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
        $allFiltered = array_values(array_filter($all, function (Metadata $metadata) {
            return $metadata->isTopLevel()
                && ($metadata->getId() >= 0)
                && ($metadata->getResourceClass() == 'books');
        }));
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
}
