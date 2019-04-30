<?php
namespace Repeka\Tests\Integration\Service;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ReproductorPermissionHelperIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ReproductorPermissionHelper */
    private $helper;
    /** @var User */
    private $testerUser;
    /** @var ResourceEntity */
    private $ebooks;
    /** @var ResourceEntity */
    private $remark;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->helper = $this->container->get(ReproductorPermissionHelper::class);
        $this->testerUser = $this->getUserByName('tester');
        $this->ebooks = $this->findResourceByContents(['Nazwa kategorii' => 'E-booki']);
        $this->remark = $this->findResourceByContents(['Nazwa uwagi' => 'Uwagi']);
    }

    public function testNoCollectionIfReproductorNowhere() {
        $this->markTestSkipped('Test is no longer valid as exists resource (remarks) which unauthorized can add subresources to');
        $this->assertEmpty($this->helper->getCollectionsWhereUserIsReproductor($this->testerUser));
        $this->assertEmpty($this->helper->getResourceKindsWhichResourcesUserCanCreate($this->testerUser));
    }

    public function testCanAddToEbooks() {
        $contents = $this->ebooks->getContents()->withReplacedValues(
            SystemMetadata::REPRODUCTOR,
            $this->testerUser->getUserData()->getId()
        );
        $this->handleCommandBypassingFirewall(new ResourceUpdateContentsCommand($this->ebooks, $contents));
        $collections = $this->helper->getCollectionsWhereUserIsReproductor($this->testerUser);
        $this->assertCount(2, $collections);
        $this->assertEquals([$this->remark->getId(), $this->ebooks->getId()], EntityUtils::mapToIds($collections));
        $resourceKinds = $this->helper->getResourceKindsWhichResourcesUserCanCreate($this->testerUser);
        $this->assertCount(3, $resourceKinds);
        return $resourceKinds;
    }

    /**
     * @depends testCanAddToEbooks
     * @param ResourceKind[] $resourceKinds
     */
    public function testCanAddBookAndForbiddenBook(array $resourceKinds) {
        $bookKind = $this->getPhpBookResource()->getKind();
        $forbiddenBookKind = $this->findResourceByContents(['tytul' => 'Mogliśmy użyć Webpacka'])->getKind();
        $this->assertContains($bookKind->getId(), EntityUtils::mapToIds($resourceKinds));
        $this->assertContains($forbiddenBookKind->getId(), EntityUtils::mapToIds($resourceKinds));
    }

    /**
     * @depends testCanAddToEbooks
     * @param ResourceKind[] $resourceKinds
     */
    public function testFilteringCollectionsByResourceKind(array $resourceKinds) {
        $collectionsWithAllowedRk = $this->helper->getCollectionsWhereUserIsReproductor($this->testerUser, $resourceKinds[1]);
        $this->assertEquals([$this->ebooks->getId()], EntityUtils::mapToIds($collectionsWithAllowedRk));
        $collectionsWithoutAllowedRk = $this->helper->getCollectionsWhereUserIsReproductor($this->testerUser, $this->ebooks->getKind());
        $this->assertEmpty($collectionsWithoutAllowedRk);
    }

    /** @large */
    public function testChangingReturnedResourceKindsAfterAllowedChildrenKindsChanges() {
        $bookKind = $this->getPhpBookResource()->getKind();
        $contents = $this->ebooks->getContents()->withReplacedValues(
            SystemMetadata::REPRODUCTOR,
            $this->testerUser->getUserData()->getId()
        );
        $this->handleCommandBypassingFirewall(new ResourceUpdateContentsCommand($this->ebooks, $contents));
        $ebooksKind = $this->ebooks->getKind();
        $metadataList = array_map(
            function (Metadata $metadata) use ($bookKind) {
                if ($metadata->getId() == SystemMetadata::PARENT) {
                    $metadata = $metadata->withOverrides(['constraints' => ['resourceKind' => [$bookKind->getId()]]]);
                }
                return $metadata;
            },
            $ebooksKind->getMetadataList()
        );
        $this->handleCommandBypassingFirewall(new ResourceKindUpdateCommand($ebooksKind, $ebooksKind->getLabel(), $metadataList));
        $this->resetEntityManager(ResourceKindRepository::class, ResourceRepository::class);
        $collections = $this->helper->getCollectionsWhereUserIsReproductor($this->testerUser);
        $this->assertCount(2, $collections);
        $this->assertEquals([$this->remark->getId(), $this->ebooks->getId()], EntityUtils::mapToIds($collections));
        $resourceKinds = $this->helper->getResourceKindsWhichResourcesUserCanCreate($this->testerUser);
        $this->assertCount(2, $resourceKinds);
        $reportedRemarkRK = $this->getResourceKindRepository()->findByName('zgloszona_uwaga');
        $this->assertEquals([$reportedRemarkRK->getId(), $bookKind->getId()], EntityUtils::mapToIds($resourceKinds));
    }
}
