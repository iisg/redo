<?php
namespace Repeka\Tests\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\Voters\ResourceClassAdminVoter;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceClassAdminVoterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceClassAdminVoter */
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
        $this->voter = new ResourceClassAdminVoter();
        $this->user = new UserEntity();
        $this->userToken = $this->createMock(TokenInterface::class);
        $this->userToken->method('getUser')->willReturn($this->user);
        $this->nullToken = $this->createMock(TokenInterface::class);
        $this->resource = $this->createResourceMock(1, $this->createResourceKindMock());
    }

    public function testAccessGrantedIfUserAdminAndMetadataPermission() {
        $this->user->updateRoles(['ADMIN-books']);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->userToken, $this->resource, ['METADATA_VISIBILITY']));
    }

    public function testAccessAbstainedIfUserIsNotAdmin() {
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->userToken, $this->resource, ['METADATA_VISIBILITY']));
    }

    public function testAccessAbstainedIfNoMetadataPermission() {
        $this->user->updateRoles(['ADMIN-books']);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->userToken, $this->resource, ['ROLE_ADMIN']));
    }

    public function testAccessGrantedIfAtLeastOneMetadataPermission() {
        $this->user->updateRoles(['ADMIN-books']);
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->userToken, $this->resource, ['ROLE_ADMIN', 'METADATA_A'])
        );
    }

    public function testAccessAbstainedIfNoUser() {
        $this->user->updateRoles(['ADMIN-books']);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->nullToken, $this->resource, ['METADATA_A']));
    }
}
