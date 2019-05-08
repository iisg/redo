<?php
namespace Repeka\Tests\Integration\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ResourceRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EntityRepository|ResourceRepository */
    private $resourceRepository;
    /** @var UserRepository */
    private $userRepository;
    /** @var Metadata */
    private $titleMetadata;
    /** @var Metadata */
    private $scannerMetadata;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->loadAllFixtures();
        $this->titleMetadata = $this->findMetadataByName('Tytuł');
        $this->scannerMetadata = $this->findMetadataByName('Skanista');
        $this->unlockAllMetadata($this->getPhpBookResource()->getWorkflow());
    }

    public function testFindByWorkflowPlaceWhenResourceHasManyWorkflowPlaces() {
        $placeA = 'aaaaaaaaa';
        $placeB = 'bbbbbbbbb';
        $book = $this->getPhpBookResource();
        $book->setMarking([$placeA => true, $placeB => 1]);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds([$placeA])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds([$placeB])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
        $query = ResourceListQuery::builder()->filterByWorkflowPlacesIds([$placeA, $placeB])->build();
        $paginatedResources = $this->resourceRepository->findByQuery($query);
        $this->assertEquals(1, $paginatedResources->getTotalCount());
    }

    public function testDelete() {
        $ebooksCategoryId = $this->getEbooksCategoryResourceId();
        $ebooksCategory = $this->resourceRepository->findOne($ebooksCategoryId);
        $countBefore = count($this->resourceRepository->findAll());
        $this->resourceRepository->delete($ebooksCategory);
        $this->getEntityManager()->flush();
        $this->assertCount($countBefore - 1, $this->resourceRepository->findAll());
        $this->assertFalse($this->resourceRepository->exists($ebooksCategoryId));
    }

    public function testFindsResourcesAssignedToUserByAssigneeMetadata() {
        $user = $this->getBudynekUser();
        $book = $this->movePhpBookResourceToBeforeScanPlace();
        $resultsBeforeAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertEmpty($resultsBeforeAssigning);
        $this->assignUserAsScanner($book, $user);
        $resultsAfterAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning)->getId());
    }

    public function testDoesNotFindTaskIfNextTransitionIsNotGuardedByAssignee() {
        $this->markTestSkipped('REPEKA-1012');
        $user = $this->getBudynekUser();
        $book = $this->getPhpBookResource();
        $resultsBeforeAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertEmpty($resultsBeforeAssigning);
        $this->assignUserAsScanner($book, $user);
        $resultsAfterAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertEmpty($resultsAfterAssigning);
    }

    public function testFindsResourcesAssignedToUserByAutoAssignMetadata() {
        $this->markTestSkipped('REPEKA-572');
        $user = $this->getBudynekUser();
        $resultsBeforeAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(0, $resultsBeforeAssigning);
        $book = $this->getPhpBookResource();
        $scannerMetadata = $this->findMetadataByName('Zeskanowane przez');
        $bookContents = $book->getContents()->withReplacedValues($scannerMetadata, $user->getUserData()->getId());
        $book->updateContents($bookContents);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $resultsAfterAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning)->getId());
    }

    public function testFindsResourcesAssignedToUserByItsGroupIdInAssigneeMetadata() {
        $user = $this->getBudynekUser();
        $book = $this->movePhpBookResourceToBeforeScanPlace();
        $resultsBeforeAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(0, $resultsBeforeAssigning);
        $scannerMetadata = $this->findMetadataByName('Skanista');
        $groupsIds = $user->getUserGroupsIds();
        $bookContents = $book->getContents()->withReplacedValues($scannerMetadata, $groupsIds[0]);
        $book->updateContents($bookContents);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $resultsAfterAssigning = $this->resourceRepository->findAssignedTo($user);
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning)->getId());
    }

    private function getEbooksCategoryResourceId(): int {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $categoryNameMetadataId = $connection->query("SELECT id FROM metadata WHERE label->'EN' = '\"Category name\"';")->fetch()['id'];
        $ebooksCategoryId = $connection
            ->query("SELECT id FROM resource WHERE contents->'{$categoryNameMetadataId}' = '[{\"value\":\"E-booki\"}]'")->fetch()['id'];
        $this->assertNotNull($ebooksCategoryId);
        return $ebooksCategoryId;
    }

    private function getBudynekUser(): UserEntity {
        /** @var UserEntity[] $users */
        $users = $this->handleCommandBypassingFirewall(new UserListQuery());
        foreach ($users as $user) {
            if ($user->getUsername() == 'budynek') {
                return $user;
            }
        }
        $this->fail("User not found");
    }

    private function assignUserAsScanner(ResourceEntity $resource, UserEntity $user) {
        $contents = $resource->getContents()->withReplacedValues($this->scannerMetadata->getId(), $user->getUserData()->getId());
        $resource->updateContents($contents);
        $this->resourceRepository->save($resource);
        $this->getEntityManager()->flush();
    }

    private function movePhpBookResourceToBeforeScanPlace(): ResourceEntity {
        $book = $this->getPhpBookResource();
        $transitionId = 'e7d756ed-d6b3-4f2f-9517-679311e88b17';
        $this->handleCommandBypassingFirewall(new ResourceTransitionCommand($book, $book->getContents(), $transitionId));
        return $book;
    }
}
