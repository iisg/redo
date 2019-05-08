<?php
namespace Repeka\Tests\Application\Security\Voters\FileVoters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\Voters\FileVoters\ResourceFileOperatorVoter;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileOperatorVoterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileOperatorVoter */
    private $voter;
    /** @var UserEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $user;
    /** @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $userToken;

    public function setUp() {
        $this->voter = new ResourceFileOperatorVoter();
        $this->user = new UserEntity();
        $this->userToken = $this->createMock(TokenInterface::class);
        $this->userToken->method('getUser')->willReturn($this->user);
    }

    /** @dataProvider userRolesCases */
    public function testUserRoles(bool $expected, array $roles, string $testName) {
        $this->user->updateRoles($roles);
        $resource = $this->createResourceMock(1);
        $resource->method('isVisibleFor')->willReturn(true);
        $actual = $this->voter->vote($this->userToken, ['resource' => $resource], ['FILE_DOWNLOAD']);
        $this->assertEquals($expected, $actual, $testName);
    }

    public function userRolesCases() {
        return [
            [VoterInterface::ACCESS_GRANTED, ['OPERATOR-books'], 'Granted when user is operator'],
            [VoterInterface::ACCESS_ABSTAIN, ['any-role'], 'Abstain when user has only other roles'],
        ];
    }
}
