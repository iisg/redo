<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\User\UserGrantRolesCommand;
use Repeka\Domain\UseCase\User\UserGrantRolesCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class UserGrantRolesCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var UserGrantRolesCommandHandler */
    private $handler;
    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var UserRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userRepository;
    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    private $user;

    private $grantedGroups;

    protected function setUp() {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->handler = new UserGrantRolesCommandHandler(
            [
                'books' => ['admins' => [['A' => 'XXX'], ['B' => 'YYY']], 'operators' => [['C' => 'ZZZ']]],
                'dictionaries' => ['admins' => [], 'operators' => [['C' => 'ZZZ']]],
            ],
            $this->userRepository,
            $this->resourceRepository,
            $this->createMetadataRepositoryStub(
                [
                    $this->createMetadataMock(1, null, null, [], 'books', [], 'A'),
                    $this->createMetadataMock(2, null, null, [], 'books', [], 'B'),
                    $this->createMetadataMock(3, null, null, [], 'books', [], 'C'),
                ]
            )
        );
        $this->user = $this->createMockEntity(User::class, 33);
        $this->user->method('getUserData')->willReturn($this->createMockEntity(ResourceEntity::class, 44));
    }

    /**
     * @dataProvider grantingRolesTestCases
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function testGrantingRoles(array $userMatchedForFilters, $expectedRoles, bool $shouldGrantRole = true) {
        $this->resourceRepository->method('findByQuery')->willReturnCallback($this->pretendToFindFor($userMatchedForFilters));
        $this->user->expects($this->once())->method('updateRoles')->willReturnCallback($this->captureGrantedGroups());
        $this->handler->handle(new UserGrantRolesCommand($this->user));
        $assertion = $shouldGrantRole ? 'assertContains' : 'assertNotContains';
        if (!is_array($expectedRoles)) {
            $expectedRoles = [$expectedRoles];
        }
        foreach ($expectedRoles as $expectedRole) {
            $this->{$assertion}($expectedRole, $this->grantedGroups);
        }
    }

    public function grantingRolesTestCases() {
        // @codingStandardsIgnoreStart
        // @formatter:off
        return [
            'no roles if no match' =>                           [[],            ['ADMIN_SOME_CLASS', 'OPERATOR_SOME_CLASS'], false],
            'admin role for books' =>                           [[[1 => 'XXX']], 'ADMIN-books'],
            'some admin role if admin for some class' =>        [[[1 => 'XXX']], 'ADMIN_SOME_CLASS'],
            'operator role if admin' =>                         [[[1 => 'XXX']], 'OPERATOR-books'],
            'no operator role for not matche class' =>          [[[1 => 'XXX']], 'OPERATOR-dictionaries', false],
            'some operator role if some admin' =>               [[[1 => 'XXX']], 'OPERATOR_SOME_CLASS'],
            'operator role' =>                                  [[[3 => 'ZZZ']], 'OPERATOR-books'],
            'some operator role if operator for some class' =>  [[[3 => 'ZZZ']], 'OPERATOR_SOME_CLASS'],
            'no admin roles if operator only' =>                [[[3 => 'ZZZ']], ['ADMIN-books', 'ADMIN_ANY_CLASS'], false],
            'all roles' => [
                [[2 => 'YYY'], [3 => 'ZZZ']],
                ['ADMIN-books', 'ADMIN_SOME_CLASS', 'OPERATOR-books', 'OPERATOR_SOME_CLASS', 'OPERATOR-dictionaries'],
            ],
        ];
        // @formatter:on
        // @codingStandardsIgnoreEnd
    }

    public function testDoesNotFailWhenConfigurationHasInvalidMetadata() {
        $handler = new UserGrantRolesCommandHandler(
            ['books' => ['admins' => [['NOT_EXISTS' => 'XXX']], 'operators' => []]],
            $this->userRepository,
            $this->resourceRepository,
            $this->createMetadataRepositoryStub(
                [
                    $this->createMetadataMock(1, null, null, [], 'books', [], 'THIS_EXISTS'),
                ]
            )
        );
        $handler->handle(new UserGrantRolesCommand($this->user));
    }

    private function pretendToFindFor(array $matchingFilters): callable {
        return function (ResourceListQuery $query) use ($matchingFilters) {
            $this->assertEquals([44], $query->getIds());
            $contentsFilters = $query->getContentsFilters();
            foreach ($matchingFilters as $matchingFilter) {
                if (ResourceContents::fromArray($matchingFilter) == $contentsFilters[0]) {
                    return new PageResult([$this->user]);
                }
            }
            return new PageResult([]);
        };
    }

    private function captureGrantedGroups() {
        return function ($groups) {
            $this->grantedGroups = $groups;
        };
    }
}
