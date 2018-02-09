<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
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
        $topLevelByResourceClass = $this->metadataRepository->findTopLevelByResourceClass('books');
        $all = $this->metadataRepository->findAll();
        $allFiltered = array_values(array_filter($all, function (Metadata $metadata) {
            return $metadata->isParent()
                && ($metadata->getId() >= 0)
                && ($metadata->getResourceClass() == 'books');
        }));
        $allIds = EntityUtils::mapToIds($topLevelByResourceClass);
        $filteredIds = EntityUtils::mapToIds($allFiltered);
        sort($allIds);
        sort($filteredIds);
        $this->assertEquals($filteredIds, $allIds);
    }
}
