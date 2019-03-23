<?php
namespace Repeka\Tests\Domain\UseCase\Assignment;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Assignment\TaskCollection;
use Repeka\Domain\UseCase\Assignment\TaskCollectionsQuery;
use Repeka\Domain\UseCase\Assignment\TaskCollectionsQueryHandler;
use Repeka\Domain\UseCase\Assignment\TasksFinder;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Tests\Traits\StubsTrait;

class TaskCollectionsQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    /** @var TaskCollectionsQueryHandler */
    private $handler;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceRepository */
    private $resourceRepository;
    /** @var TasksFinder|PHPUnit_Framework_MockObject_MockObject */
    private $tasksFinder;
    /** @var User|PHPUnit_Framework_MockObject_MockObject */
    private $user;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->tasksFinder = $this->createMock(TasksFinder::class);
        $this->handler = new TaskCollectionsQueryHandler($this->resourceRepository, $this->tasksFinder);
        $this->user = $this->createMock(User::class);
        $this->tasksFinder->method('getAllTasks')->willReturn(
            [
                'books' => [
                    'own' => $this->resources([10, 11, 12]),
                    'possible' => $this->resources([20, 21, 22]),
                ],
                'cms' => [
                    'own' => $this->resources([30, 31, 32]),
                ],
            ]
        );
    }

    public function testReturnsAllTasks() {
        $query = TaskCollectionsQuery::builder()->forUser($this->user)->build();
        $result = $this->handler->handle($query);
        $expected = [
            new TaskCollection('books', TaskStatus::OWN(), new PageResult($this->resources([10, 11, 12]), 3)),
            new TaskCollection('books', TaskStatus::POSSIBLE(), new PageResult($this->resources([20, 21, 22]), 3)),
            new TaskCollection('cms', TaskStatus::OWN(), new PageResult($this->resources([30, 31, 32]), 3)),
        ];
        $this->assertEquals($expected, $result);
    }

    public function testReturnsOnlyQueriedCollections() {
        $this->resourceRepository->method('findByQuery')->willReturnCallback(
            function (ResourceListQuery $query) {
                if ($query->getIds() == [10, 11, 12]) {
                    return new PageResult($this->resources([10, 11]), 2);
                }
                $this->fail('Should not filter collection not in query');
            }
        );
        $listQuery = ResourceListQuery::builder()->build();
        $query = TaskCollectionsQuery::builder()
            ->forUser($this->user)
            ->onlyQueriedCollections()
            ->addSingleCollectionQuery('books', 'own', $listQuery)
            ->build();
        $result = $this->handler->handle($query);
        $expected = [new TaskCollection('books', TaskStatus::OWN(), new PageResult($this->resources([10, 11]), 2))];
        $this->assertEquals($expected, $result);
    }

    public function testReturnsAllCollections() {
        $this->resourceRepository->method('findByQuery')->willReturnCallback(
            function (ResourceListQuery $query) {
                if ($query->getIds() == [10, 11, 12]) {
                    return new PageResult($this->resources([10, 11]), 2);
                }
                $this->fail('Should not filter other collections not in query');
            }
        );
        $listQuery = ResourceListQuery::builder()->build();
        $query = TaskCollectionsQuery::builder()
            ->forUser($this->user)
            ->addSingleCollectionQuery('books', 'own', $listQuery)
            ->build();
        $result = $this->handler->handle($query);
        $expected = [
            new TaskCollection('books', TaskStatus::OWN(), new PageResult($this->resources([10, 11]), 2)),
            new TaskCollection('books', TaskStatus::POSSIBLE(), new PageResult($this->resources([20, 21, 22]), 3)),
            new TaskCollection('cms', TaskStatus::OWN(), new PageResult($this->resources([30, 31, 32]), 3)),
        ];
        $this->assertEquals($expected, $result);
    }

    public function testIgnoresQueriesForNonExistingTasks() {
        $listQuery = ResourceListQuery::builder()->build();
        $query = TaskCollectionsQuery::builder()
            ->forUser($this->user)
            ->onlyQueriedCollections()
            ->addSingleCollectionQuery('notExistingRC', TaskStatus::POSSIBLE, $listQuery)
            ->build();
        $result = $this->handler->handle($query);
        $this->assertEquals([], $result);
    }

    private function resources($ids) {
        return array_map(
            function ($id) {
                return $this->createResourceMock($id);
            },
            $ids
        );
    }
}
