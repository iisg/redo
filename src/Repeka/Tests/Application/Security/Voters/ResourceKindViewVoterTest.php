<?php
namespace Repeka\Tests\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\Voters\ResourceKindViewVoter;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceKindViewVoterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindViewVoter */
    private $voter;
    /** @var UserEntity */
    private $user;
    /** @var TokenInterface */
    private $userToken;
    /** @var TokenInterface */
    private $nullToken;
    /** @var ResourceEntity */
    private $resource;

    public function setUp() {
        $helper = $this->createMock(ReproductorPermissionHelper::class);
        $helper->method('getResourceKindsWhichResourcesUserCanCreate')->willReturn(
            [
                $this->createResourceKindMock(1),
                $this->createResourceKindMock(2),
            ]
        );
        $mockedUser = $this->createMock(UserEntity::class);
        $mockedUser->method('getGroupIdsWithUserId')->willReturn([-1]);
        $unauthenticatedUserPermissionHelper = $this->createMock(UnauthenticatedUserPermissionHelper::class);
        $unauthenticatedUserPermissionHelper->method('getUnauthenticatedUser')->willReturn($mockedUser);
        $this->voter = new ResourceKindViewVoter($helper, $unauthenticatedUserPermissionHelper);
        $this->user = new UserEntity();
        $this->userToken = $this->createMock(TokenInterface::class);
        $this->userToken->method('getUser')->willReturn($this->user);
        $this->nullToken = $this->createMock(TokenInterface::class);
        $this->resource = $this->createResourceMock(1, $this->createResourceKindMock());
    }

    public function testAccessGrantedIfAskedForRkUserCanCreate() {
        $this->assertTrue($this->voter->voteOnAttribute('VIEW', $this->createResourceKindMock(1), $this->userToken));
    }

    public function testAccessGrantedIfAskedForRkUnauthenticatedCanCreate() {
        $this->assertTrue($this->voter->voteOnAttribute('VIEW', $this->createResourceKindMock(1), $this->nullToken));
    }

    public function testAccessDeniedIfAskedForRkUserCannotCreate() {
        $this->assertFalse($this->voter->voteOnAttribute('VIEW', $this->createResourceKindMock(3), $this->userToken));
    }

    public function testSupportsViewPermissionOnly() {
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->userToken, $this->createResourceKindMock(1), ['VIEW'])
        );
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->userToken, $this->createResourceKindMock(3), ['VIEW'])
        );
        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->userToken, $this->createResourceKindMock(1), ['EDIT'])
        );
    }
}
