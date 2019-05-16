<?php
namespace Repeka\Tests\Integration\UseCase\Task;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Task\TasksFinder;
use Repeka\Domain\UseCase\User\UserListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class TaskFinderIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var TasksFinder */
    private $taskFinder;
    /** @var Metadata */
    private $scannerMetadata;

    public function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
        $this->taskFinder = $this->container->get(TasksFinder::class);
        $this->loadAllFixtures();
        $this->scannerMetadata = $this->findMetadataByName('Skanista');
        $this->unlockAllMetadata($this->getPhpBookResource()->getWorkflow());
    }

    public function testFindsResourcesAssignedToUserByAssigneeMetadata() {
        $user = $this->getBudynekUser();
        $book = $this->movePhpBookResourceToBeforeScanPlace();
        $resultsBeforeAssigning = $this->taskFinder->getTasksIdsForUserOnly($user, 'books');
        $this->assertEmpty($resultsBeforeAssigning);
        $this->assignUserAsScanner($book, $user);
        $resultsAfterAssigning = $this->taskFinder->getTasksIdsForUserOnly($user, 'books');
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning));
    }

    public function testDoesNotFindTaskIfNextTransitionIsNotGuardedByAssignee() {
        $user = $this->getBudynekUser();
        $book = $this->getPhpBookResource();
        $resultsBeforeAssigning = $this->taskFinder->getTasksIdsForUserOnly($user, 'books');
        $this->assertEmpty($resultsBeforeAssigning);
        $this->assignUserAsScanner($book, $user);
        $resultsAfterAssigning = $this->taskFinder->getTasksIdsForUserOnly($user, 'books');
        $this->assertEmpty($resultsAfterAssigning);
    }

    public function testFindsResourcesAssignedToUserByAutoAssignMetadata() {
        $user = $this->getBudynekUser();
        $book = $this->movePhpBookResourceToBeforeScanPlace();
        $resultsBeforeAssigning = $this->taskFinder->getTasksIdsForUserOnly($user, 'books');
        $this->assertCount(0, $resultsBeforeAssigning);
        $bookContents = $book->getContents()->withReplacedValues($this->scannerMetadata, $user->getUserData()->getId());
        $book->updateContents($bookContents);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $resultsAfterAssigning = $this->taskFinder->getTasksIdsForUserOnly($user, 'books');
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning));
    }

    public function testFindsResourcesAssignedToUserByItsGroupIdInAssigneeMetadata() {
        $user = $this->getBudynekUser();
        $book = $this->movePhpBookResourceToBeforeScanPlace();
        $resultsBeforeAssigning = $this->taskFinder->getTasksIdsForUserGroupsOnly($user, 'books');
        $this->assertCount(0, $resultsBeforeAssigning);
        $groupsIds = $user->getUserGroupsIds();
        $bookContents = $book->getContents()->withReplacedValues($this->scannerMetadata, $groupsIds[0]);
        $book->updateContents($bookContents);
        $this->resourceRepository->save($book);
        $this->getEntityManager()->flush();
        $resultsAfterAssigning = $this->taskFinder->getTasksIdsForUserGroupsOnly($user, 'books');
        $this->assertCount(1, $resultsAfterAssigning);
        $this->assertEquals($book->getId(), reset($resultsAfterAssigning));
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
