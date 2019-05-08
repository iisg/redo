<?php
namespace Repeka\Plugins\Redo\Tests\Security\Voters\FileVoters;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Plugins\Redo\Security\Voters\FileVoters\ResourceFileByAddressIpVoter;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileByAddressIpVoterTest extends PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileByAddressIpVoter */
    private $voter;
    /** @var UserEntity */
    private $user;
    /** @var TokenInterface */
    private $userToken;
    /** @var TokenInterface */
    private $nullToken;
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceEntity */
    private $resourceForEveryone;
    /** @var ResourceEntity */
    private $resourceNotAllowed;
    /** @var MetadataRepository|PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var ResourceRepository|PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    private $request;

    public function setUp() {
        $this->request = $this->createMock(Request::class);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($this->request);
        $allowedAddrIpsMetadata = $this->createMetadataMock(1, null, MetadataControl::TEXT(), [], 'cms', [], 'dozwolony_adres_ip');
        $allowedRightsMetadata = $this->createMetadataMock(
            2,
            null,
            MetadataControl::TEXT(),
            ['resourceKind' => [$allowedAddrIpsMetadata->getId()]],
            'books',
            [],
            'prawa_dostepu'
        );
        $this->metadataRepository = $this->createMetadataRepositoryStub([$allowedRightsMetadata, $allowedAddrIpsMetadata]);
        $allowedRightsResourceKind = $this->createResourceKindMock(1, 'cms', [$allowedAddrIpsMetadata]);
        $allowedRightsDictionary = $this->createResourceMock(
            1,
            $allowedRightsResourceKind,
            [$allowedAddrIpsMetadata->getId() => ['127.0.0.1', '10.5.16.0/20']]
        );
        $allowedForEveryone = $this->createResourceMock(
            2,
            $allowedRightsResourceKind,
            [$allowedAddrIpsMetadata->getId() => '0.0.0.0/0']
        );
        $allowedForNoOne = $this->createResourceMock(
            3,
            $allowedRightsResourceKind,
            [$allowedAddrIpsMetadata->getId() => '0.0.0.0/32']
        );
        $this->resourceRepository = $this->createResourceRepositoryStub([$allowedRightsDictionary, $allowedForEveryone, $allowedForNoOne]);
        $this->voter = new ResourceFileByAddressIpVoter($this->metadataRepository, $this->resourceRepository, $requestStack);
        $this->user = new UserEntity();
        $this->userToken = $this->createMock(TokenInterface::class);
        $this->userToken->method('getUser')->willReturn($this->user);
        $this->nullToken = $this->createMock(TokenInterface::class);
        $resourceKind = $this->createResourceKindMock(2, 'books', [$allowedRightsMetadata]);
        $this->resource = $this->createResourceMock(
            2,
            $resourceKind,
            [$allowedRightsMetadata->getId() => $allowedRightsDictionary->getId()]
        );
        $this->resourceForEveryone = $this->createResourceMock(
            3,
            $resourceKind,
            [$allowedRightsMetadata->getId() => $allowedForEveryone->getId()]
        );
        $this->resourceNotAllowed = $this->createResourceMock(
            4,
            $resourceKind,
            [$allowedRightsMetadata->getId() => $allowedForNoOne->getId()]
        );
    }

    public function testAccesGrantedIfUserIsNotAdminAndIpIsInMetadataValues() {
        $this->request->method('getClientIp')->willReturn('127.0.0.1');
        $voterDecision = $this->voter->voteOnAccessToFile(
            $this->userToken,
            $this->resource
        );
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterDecision);
    }

    public function testAccessGrantedIfUserIsNotAdminAndIpIsInSubnet() {
        $this->request->method('getClientIp')->willReturn('10.5.21.30');
        $voterDecision = $this->voter->voteOnAccessToFile(
            $this->userToken,
            $this->resource
        );
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterDecision);
    }

    public function testAccessGrantedForEveryone() {
        $this->request->method('getClientIp')->willReturn('10.5.21.30');
        $voterDecision = $this->voter->voteOnAccessToFile(
            $this->userToken,
            $this->resourceForEveryone
        );
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voterDecision);
    }

    public function testAccessDeniedForEveryone() {
        $this->request->method('getClientIp')->willReturn('10.5.21.30');
        $voterDecision = $this->voter->voteOnAccessToFile(
            $this->userToken,
            $this->resourceNotAllowed
        );
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterDecision);
    }

    public function testAccessDeniedIfUserIsNotAdminAndIpIsNotInMetadataValues() {
        $this->request->method('getClientIp')->willReturn('127.0.0.2');
        $voterDecision = $this->voter->voteOnAccessToFile(
            $this->userToken,
            $this->resource
        );
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voterDecision);
    }

    public function testAbstainIfNoRequest() {
        $voter = new ResourceFileByAddressIpVoter(
            $this->metadataRepository,
            $this->resourceRepository,
            $this->createMock(RequestStack::class)
        );
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->voteOnAccessToFile($this->userToken, $this->resource));
    }

    /** @dataProvider networkExamples */
    public function testCidrMatch($ip, $subnet, $expectedMatch) {
        $this->assertEquals($expectedMatch, $this->voter->cidrMatch($ip, $subnet));
    }

    public function networkExamples() {
        return [
            ['10.5.21.30', '10.5.16.0/20', true],
            ['127.0.0.1', '127.0.0.1/32', true],
            ['127.0.0.1', '127.0.0.1/31', true],
            ['127.0.0.1', '127.0.0.1/0', true],
            ['149.156.100.1', '149.156.100.1/24', true],
            ['149.156.100.100', '149.156.100.1/24', true],
            ['149.156.100.254', '149.156.100.1/24', true],
            ['149.156.101.254', '149.156.100.1/24', false],
        ];
    }
}
