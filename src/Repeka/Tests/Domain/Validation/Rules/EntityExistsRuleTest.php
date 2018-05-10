<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\RepositoryProvider;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Tests\Traits\StubsTrait;

class EntityExistsRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $alwaysExistsRepository;
    private $neverExistsRepository;

    protected function setUp() {
        $this->alwaysExistsRepository = $this->createMock(DummyRepository::class);
        $this->alwaysExistsRepository->method('findOne')->willReturn(new \stdClass());
        $this->neverExistsRepository = $this->createMock(DummyRepository::class);
        $this->neverExistsRepository->method('findOne')->willThrowException(new EntityNotFoundException('dummy', 0));
    }

    public function testAcceptsWhenEntityExists() {
        $repositoryProvider = $this->createRepositoryProviderMock($this->alwaysExistsRepository);
        $validator = (new EntityExistsRule($repositoryProvider))->forEntityType('dummy');
        $this->assertTrue($validator->validate(0));
    }

    public function testRejectsWhenEntityDoesNotExist() {
        $repositoryProvider = $this->createRepositoryProviderMock($this->neverExistsRepository);
        $validator = (new EntityExistsRule($repositoryProvider))->forEntityType('dummy');
        $this->assertFalse($validator->validate(0));
    }

    public function testQueriesCorrectId() {
        $testId = 1234;
        $repository = $this->createMock(DummyRepository::class);
        $repository->expects($this->once())->method('findOne')->willReturnCallback(
            function ($id) use ($testId) {
                $this->assertEquals($testId, $id);
                return new \stdClass();
            }
        );
        $repositoryProvider = $this->createRepositoryProviderMock($repository);
        $validator = (new EntityExistsRule($repositoryProvider))->forEntityType('dummy');
        $this->assertTrue($validator->validate($testId));
    }

    /** @return RepositoryProvider|\PHPUnit_Framework_MockObject_MockObject */
    private function createRepositoryProviderMock($repository): RepositoryProvider {
        $mock = $this->createMock(RepositoryProvider::class);
        $mock->method('getForEntityType')->willReturn($repository);
        return $mock;
    }
}

// @codingStandardsIgnoreStart
class DummyRepository {
    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function findOne($id) {
    }
}
