<?php
namespace Repeka\Tests\Integration\Repository;

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

    public function testFindAllBase() {
        $allBase = $this->metadataRepository->findAllBase();
        $all = $this->metadataRepository->findAll();
        $allFiltered = array_values(array_filter($all, function (Metadata $metadata) {
            return $metadata->isBase() && $metadata->isParent() && ($metadata->getId() >= 0);
        }));
        $baseIds = array_map(function (Metadata $metadata) {
            return $metadata->getId();
        }, $allBase);
        $filteredIds = array_map(function (Metadata $metadata) {
            return $metadata->getId();
        }, $allFiltered);
        sort($baseIds);
        sort($filteredIds);
        $this->assertEquals($filteredIds, $baseIds);
    }

    public function testFindAllBaseByResourceClass() {
        $allBaseByResourceClass = $this->metadataRepository->findAllBaseByResourceClass('books');
        $all = $this->metadataRepository->findAll();
        $allFiltered = array_values(array_filter($all, function (Metadata $metadata) {
            return $metadata->isBase()
            && $metadata->isParent()
            && ($metadata->getId() >= 0)
            && ($metadata->getResourceClass() == 'books');
        }));
        $baseIds = array_map(function (Metadata $metadata) {
            return $metadata->getId();
        }, $allBaseByResourceClass);
        $filteredIds = array_map(function (Metadata $metadata) {
            return $metadata->getId();
        }, $allFiltered);
        sort($baseIds);
        $this->assertEquals($filteredIds, $baseIds);
    }
}
