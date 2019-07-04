<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ResourceRepositoryFindByContentsIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $phpBook;
    /** @var Metadata */
    private $titleMetadata;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    /** @before */
    public function init() {
        $this->phpBook = $this->getPhpBookResource();
        $this->titleMetadata = $this->findMetadataByName('Tytuł');
    }

    public function testFixtureDataHasNotChanged() {
        $message = 'You have changed the contents of PHP Book added in fixtures. Tests in this class strongly relies on its contents.';
        $this->assertEquals(['PHP - to można leczyć!'], $this->phpBook->getValues($this->titleMetadata), $message);
    }

    public function testFindByFullMetadataValue() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'PHP - to można leczyć!'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
        $this->assertEquals($this->phpBook->getId(), $results[0]->getId());
    }

    public function testFindBySubstring() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'PHP'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testCaseInsensitive() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'php'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testAnchorStart() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => '^PHP'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testAnchorEnd() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'leczyć!$'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testDoesNotFindSubstringWithBothAnchors() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => '^PHP$'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertEmpty($results);
    }

    public function testWildcard() {
        $query = ResourceListQuery::builder()->filterByContents([$this->titleMetadata->getId() => 'P.*leczy.!'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
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
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByTwoValuesOfTheSameMetadata() {
        $query = ResourceListQuery::builder()->filterByContents(
            [
                $this->titleMetadata->getId() => ['PHP', 'Webpack'],
            ]
        )->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(3, $results);
    }

    public function testFindsWithAdditionalResourceClassFilter() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClass($this->phpBook->getResourceClass())
            ->filterByContents([$this->titleMetadata->getId() => 'PHP'])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
    }

    public function testDoesNotFindIfResourceClassDoesNotMatch() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('users')
            ->filterByContents([$this->titleMetadata->getId() => 'PHP'])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertEmpty($results);
    }

    public function testFindByUsername() {
        $query = ResourceListQuery::builder()
            ->filterByResourceKind($this->getAdminUser()->getUserData()->getKind())
            ->filterByContents([SystemMetadata::USERNAME => 'admin'])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
    }

    public function testFindByNumber() {
        $query = ResourceListQuery::builder()
            ->filterByContents([$this->findMetadataByName('Liczba stron')->getId() => 404])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
    }

    public function testFindByNumberDoesNotAcceptNumberSubstring() {
        $query = ResourceListQuery::builder()
            ->filterByContents([$this->findMetadataByName('Liczba stron')->getId() => 40])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertEmpty($results);
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
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByMetadataName() {
        $query = ResourceListQuery::builder()->filterByContents(['Tytuł' => 'PHP'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->phpBook->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByAlternatives() {
        $descriptionMetadata = $this->findMetadataByName('Opis');
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('books')
            ->filterByContents([$this->titleMetadata->getId() => 'PHP'])
            ->filterByContents([$descriptionMetadata->getId() => 'poradnik'])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(3, $results);
        $this->assertContains($this->getPhpBookResource()->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByAlternativesUsingSameMetadata() {
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('books')
            ->filterByContents([$this->titleMetadata->getId() => 'PHP'])
            ->filterByContents([$this->titleMetadata->getId() => 'MySQL'])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->getPhpBookResource()->getId(), EntityUtils::mapToIds($results));
    }

    public function testConditionsFromOneAlternativeDoNotApplyToAnother() {
        $descriptionMetadata = $this->findMetadataByName('Opis');
        $query = ResourceListQuery::builder()
            ->filterByResourceClass('books')
            ->filterByContents([$this->titleMetadata->getId() => 'PHP', $descriptionMetadata->getId() => 'poradnik'])
            ->filterByContents([$this->titleMetadata->getId() => 'MySQL'])
            ->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $this->assertContains($this->getPhpBookResource()->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByReproductor() {
        $admin = $this->getAdminUser();
        $category = $this->findResourceByContents([$this->findMetadataByName('Nazwa kategorii')->getId() => 'E-booki']);
        $this->assertNotEmpty(
            array_intersect($admin->getUserGroupsIds(), $category->getContents()->getValuesWithoutSubmetadata(SystemMetadata::REPRODUCTOR))
        );
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::REPRODUCTOR => $admin->getUserGroupsIds()])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertNotEmpty($results);
        $this->assertContains($category->getId(), EntityUtils::mapToIds($results));
    }

    public function testFindByParentAsStringSearchesForExactValue() {
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::PARENT => '1'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertEmpty($results); // the '1' query should not match resources that have parent 16 etc
    }

    public function testFindByParentAsIntSearchesForExactValue() {
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::PARENT => 1])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertEmpty($results); // the 1 query should not match resources that have parent 16 etc
    }

    public function testFindingByParent() {
        $ebooks = $this->findResourceByContents(['nazwaKategorii' => 'E-booki']);
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::PARENT => "{$ebooks->getId()}"])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
    }

    public function testFindingByDateRegex() {
        $query = ResourceListQuery::builder()->filterByContents(['data_utworzenia_rekordu' => '2.+'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(5, $results);
    }

    public function testFindingByDateCondition() {
        $query = ResourceListQuery::builder()->filterByContents(['data_utworzenia_rekordu' => '>2000-01-01'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(5, $results);
    }

    public function testFindingByDateRange() {
        $query = ResourceListQuery::builder()->filterByContents(['data_utworzenia_rekordu' => [['>2000-01-01', '<2015-01-01']]])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(0, $results);
    }

    public function testFilteringByExcludedMetadata() {
        $excludedMetadata = $this->getMetadataRepository()->findByName('opis');
        $resources = $this->getResourceRepository()->findByQuery(
            ResourceListQuery::builder()
                ->filterByResourceKind($this->getPhpBookResource()->getKind())
                ->filterByContents([$excludedMetadata->getId() => null])
                ->build()
        )->getResults();
        /** @var ResourceEntity $resource */
        foreach ($resources as $resource) {
            $metadataIds = array_keys($resource->getContents()->toArray());
            $this->assertNotContains($excludedMetadata->getId(), $metadataIds);
        }
    }

    public function testFilteringByExcludedMetadataWithNullString() {
        $excludedMetadata = $this->getMetadataRepository()->findByName('opis');
        $resources = $this->getResourceRepository()->findByQuery(
            ResourceListQuery::builder()
                ->filterByResourceKind($this->getPhpBookResource()->getKind())
                ->filterByContents([$excludedMetadata->getId() => 'null'])
                ->build()
        )->getResults();
        /** @var ResourceEntity $resource */
        foreach ($resources as $resource) {
            $metadataIds = array_keys($resource->getContents()->toArray());
            $this->assertNotContains($excludedMetadata->getId(), $metadataIds);
        }
    }

    public function testFindingByInteger() {
        $query = ResourceListQuery::builder()->filterByContents(['liczba_stron' => '404'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
    }

    public function testFindingByIntegerIsExact() {
        $query = ResourceListQuery::builder()->filterByContents(['liczba_stron' => '4'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(0, $results);
    }

    public function testFindingByIntegerGreaterThanComparesIntValue() {
        $query = ResourceListQuery::builder()->filterByContents(['liczba_stron' => '>9'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(2, $results);
        $query = ResourceListQuery::builder()->filterByContents(['liczba_stron' => '<500'])->build();
        $results = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(1, $results);
    }
}
