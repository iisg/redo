<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class ResourceRepositoryFindByContentsIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $phpBook;
    /** @var Metadata */
    private $titleMetadata;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->phpBook = $this->getPhpBookResource();
        $this->titleMetadata = $this->findMetadataByName('Tytuł');
    }

    public function testFixtureDataHasNotChanged() {
        $message = 'You have changed the contents of PHP Book added in fixtures. Tests in this class strongly relies on its contents.';
        $this->assertEquals(['PHP - to można leczyć!'], $this->phpBook->getContents()->getValues($this->titleMetadata), $message);
    }

    public function testFindByFullMetadataValue() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'PHP - to można leczyć!'])->build();
        $results = $this->handleCommand($query);
        $this->assertCount(1, $results);
        $this->assertEquals($this->phpBook->getId(), $results[0]->getId());
    }

    public function testFindBySubstring() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'PHP'])->build();
        $results = $this->handleCommand($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testCaseInsensitive() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'php'])->build();
        $results = $this->handleCommand($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByTwoMetadata() {
        $descriptionMetadata = $this->findMetadataByName('Opis');
        $query = ResourceListQuery::builder()->filterByContents(
            [
                $this->titleMetadata->getId() => 'PHP',
                $descriptionMetadata->getId() => 'poradnik',
            ]
        )->build();
        $results = $this->handleCommand($query);
        $this->assertCount(1, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindsWithAdditionalResourceClassFilter() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClass($this->phpBook->getResourceClass())
            ->filterByContents([$this->titleMetadata->getId() => 'PHP'])
            ->build();
        $results = $this->handleCommand($query);
        $this->assertCount(2, $results);
    }

    public function testDoesNotFindIfResourceClassDoesNotMatch() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('users')
            ->filterByContents([$this->titleMetadata->getId() => 'PHP'])
            ->build();
        $results = $this->handleCommand($query);
        $this->assertEmpty($results);
    }

    public function testFindByUsername() {
        $query = ResourceListQuery::builder()
            ->filterByResourceKind($this->getAdminUser()->getUserData()->getKind())
            ->filterByContents([SystemMetadata::USERNAME => 'admin'])
            ->build();
        $results = $this->handleCommand($query);
        $this->assertCount(1, $results);
    }

    public function testFindByManyConditions() {
        $descriptionMetadata = $this->findMetadataByName('Opis');
        $query = ResourceListQuery::builder()
            ->filterByResourceClass($this->phpBook->getResourceClass())
            ->filterByResourceKind($this->phpBook->getKind())
            ->onlyTopLevel()
            ->filterByContents(
                [
                    $this->titleMetadata->getId() => 'PHP',
                    $descriptionMetadata->getId() => 'poradnik',
                ]
            )->build();
        $results = $this->handleCommand($query);
        $this->assertCount(1, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }
}
