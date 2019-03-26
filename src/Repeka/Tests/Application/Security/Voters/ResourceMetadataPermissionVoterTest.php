<?php
namespace Repeka\Tests\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\Voters\ResourceMetadataPermissionVoter;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ResourceMetadataPermissionVoterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceMetadataPermissionVoter */
    private $voter;
    /** @var UserEntity */
    private $user;
    /** @var TokenInterface */
    private $userToken;
    /** @var TokenInterface */
    private $nullToken;
    /** @var ResourceKind */
    private $resourceKind;

    public function setUp() {
        $this->resourceKind = $this->createResourceKindMock();
        $metadataRepository = $this->createMetadataRepositoryStub([$this->createMetadataMock(1, null, null, [], 'books', [], 'unicorn')]);
        $mockedUser = $this->createMock(UserEntity::class);
        $mockedUser->method('getGroupIdsWithUserId')->willReturn([-1]);
        $unauthenticatedUserPermissionHelper = $this->createMock(UnauthenticatedUserPermissionHelper::class);
        $unauthenticatedUserPermissionHelper->method('getUnauthenticatedUser')->willReturn($mockedUser);
        $this->voter = new ResourceMetadataPermissionVoter($metadataRepository, $unauthenticatedUserPermissionHelper);
        $this->user = new UserEntity();
        $userData = $this->createResourceMock(
            1024,
            $this->resourceKind,
            ResourceContents::fromArray([SystemMetadata::GROUP_MEMBER => 2048])
        );
        $this->user->setUserData($userData);
        $this->userToken = $this->createMock(TokenInterface::class);
        $this->userToken->method('getUser')->willReturn($this->user);
        $this->nullToken = $this->createMock(TokenInterface::class);
    }

    public function testGettingMetadataNameFromPermission() {
        $this->assertEquals('unicorn', ResourceMetadataPermissionVoter::getMetadataNameFromPermission('METADATA_UNICORN'));
        $this->assertEquals('some_metadata', ResourceMetadataPermissionVoter::getMetadataNameFromPermission('METADATA_SOME_METADATA'));
        $this->assertEquals('some_metadata', ResourceMetadataPermissionVoter::getMetadataNameFromPermission('METADATA_someMetadata'));
    }

    public function testCheckingForMetadataPermission() {
        $this->assertTrue(ResourceMetadataPermissionVoter::isMetadataPermission('METADATA_UNICORN'));
        $this->assertTrue(ResourceMetadataPermissionVoter::isMetadataPermission('METADATA_SOME_METADATA'));
        $this->assertTrue(ResourceMetadataPermissionVoter::isMetadataPermission('METADATA_someMetadata'));
        $this->assertFalse(ResourceMetadataPermissionVoter::isMetadataPermission('SOME_METADATA'));
        $this->assertFalse(ResourceMetadataPermissionVoter::isMetadataPermission('ROLE_METADATA'));
    }

    public function testCreatingMetadataPermissionName() {
        $this->assertEquals(
            'METADATA_VISIBILITY',
            ResourceMetadataPermissionVoter::createMetadataPermissionName(SystemMetadata::VISIBILITY())
        );
        $this->assertEquals(
            'METADATA_visibility',
            ResourceMetadataPermissionVoter::createMetadataPermissionName(SystemMetadata::VISIBILITY()->toMetadata())
        );
        $this->assertEquals('METADATA_visibility', ResourceMetadataPermissionVoter::createMetadataPermissionName('visibility'));
    }

    public function testUserCanViewResourceWithHisIdByCustomMetadata() {
        $contents = ResourceContents::fromArray([1 => [$this->user->getUserData()->getId()]]);
        $resource = new ResourceEntity($this->resourceKind, $contents);
        $this->assertTrue($this->voter->voteOnAttribute($this->permissionName('unicorn'), $resource, $this->userToken));
    }

    public function testUserCanViewResourceWithHisIdByVisibilityMetadata() {
        $contents = ResourceContents::fromArray([SystemMetadata::VISIBILITY => [$this->user->getUserData()->getId()]]);
        $resource = new ResourceEntity($this->resourceKind, $contents);
        $this->assertTrue($this->voter->voteOnAttribute($this->permissionName(SystemMetadata::VISIBILITY()), $resource, $this->userToken));
    }

    public function testUserCanViewResourceWithHisGroupId() {
        $contents = ResourceContents::fromArray([SystemMetadata::VISIBILITY => $this->user->getUserGroupsIds()]);
        $resource = new ResourceEntity($this->resourceKind, $contents);
        $this->assertTrue($this->voter->voteOnAttribute($this->permissionName(SystemMetadata::VISIBILITY()), $resource, $this->userToken));
    }

    public function testUnauthenticatedUserCanViewPublicResource() {
        $contents = ResourceContents::fromArray([SystemMetadata::TEASER_VISIBILITY => [SystemResource::UNAUTHENTICATED_USER]]);
        $resource = new ResourceEntity($this->resourceKind, $contents);
        $this->assertTrue(
            $this->voter->voteOnAttribute($this->permissionName(SystemMetadata::TEASER_VISIBILITY()), $resource, $this->nullToken)
        );
    }

    public function testUserCannotSeeInvisibleResource() {
        $contents = ResourceContents::fromArray([SystemMetadata::VISIBILITY => []]);
        $resource = new ResourceEntity($this->resourceKind, $contents);
        $this->assertFalse($this->voter->voteOnAttribute($this->permissionName(SystemMetadata::VISIBILITY()), $resource, $this->userToken));
    }

    public function testUnauthenticatedUserCannotSeeInvisibleResource() {
        $contents = ResourceContents::fromArray([SystemMetadata::VISIBILITY => []]);
        $resource = new ResourceEntity($this->resourceKind, $contents);
        $this->assertFalse($this->voter->voteOnAttribute($this->permissionName(SystemMetadata::VISIBILITY()), $resource, $this->nullToken));
    }

    private function permissionName($metadata): string {
        return ResourceMetadataPermissionVoter::createMetadataPermissionName($metadata);
    }
}
