<?php
namespace Repeka\Tests\Application\Serialization;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\SecurityOracle;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceNormalizerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var TransitionPossibilityChecker|\PHPUnit_Framework_MockObject_MockObject */
    private $checker;

    /** @var ResourceNormalizer */
    private $normalizer;
    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    private $user;
    private $resourceWithChild;
    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var  SecurityOracle|\PHPUnit_Framework_MockObject_MockObject */
    private $securityOracle;

    protected function setUp() {
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createResourceMock(1, $this->createResourceKindMock(1, 'books', [], $this->workflow));
        $this->resourceWithChild = $this->createResourceMock(2);
        // TokenStorage
        $this->user = $this->createMock(UserEntity::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);
        $tokenStorage = $this->createMock(TokenStorage::class);
        $tokenStorage->method('getToken')->willReturn($token);
        // TransitionPossibilityChecker
        $this->checker = $this->createMock(TransitionPossibilityChecker::class);
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->securityOracle = $this->createMock(SecurityOracle::class);
        // test subject
        $this->normalizer = new ResourceNormalizer($this->checker, $this->resourceRepository, $this->securityOracle);
        $this->normalizer->setTokenStorage($tokenStorage);
        $normalizerService = $this->createMock(NormalizerInterface::class);
        $normalizerService->method('normalize')->willReturnArgument(0);
        $this->normalizer->setNormalizer($normalizerService);
    }

    /** @SuppressWarnings("PHPMD.UnusedLocalVariable") */
    public function testGettingBlockedTransitions() {
        $this->securityOracle->method('hasMetadataPermission')->willReturn(true);
        $this->workflow->method('getTransitions')->willReturn(
            [
                $this->transition('a'),
                $this->transition('b'),
                $this->transition('c'),
            ]
        );
        $this->checker->method('check')->willReturnCallback(
            function ($resource, $resourceContents, ResourceWorkflowTransition $transition, $user) {
                return ($transition->getId() == 'a')
                    ? new TransitionPossibilityCheckResult([], false)
                    : new TransitionPossibilityCheckResult([], true);
            }
        );
        $normalized = $this->normalizer->normalize($this->resource);
        $this->assertArrayHasKey('blockedTransitions', $normalized);
        $blockedTransitions = $normalized['blockedTransitions'];
        $this->assertCount(3, $blockedTransitions);
        $this->assertArrayHasKey('b', $blockedTransitions);
        $this->assertArrayHasKey('c', $blockedTransitions);
        $this->assertArrayHasKey('update', $blockedTransitions);
    }

    public function testGettingAvailableTransitions() {
        $this->securityOracle->method('hasMetadataPermission')->willReturn(true);
        $this->workflow->method('getTransitions')->willReturn([$this->transition('a')]);
        $this->checker->method('check')->willReturn(new TransitionPossibilityCheckResult([], false));
        $normalized = $this->normalizer->normalize($this->resource);
        $this->assertArrayHasKey('availableTransitions', $normalized);
        $availableTransitions = $normalized['availableTransitions'];
        $this->assertCount(2, $availableTransitions);
        $this->assertEquals(['a', 'update'], EntityUtils::mapToIds($availableTransitions));
    }

    public function testGettingAvailableTransitionsForResourceWithoutWorkflow() {
        $this->securityOracle->method('hasMetadataPermission')->willReturn(true);
        $resource = $this->createResourceMock(1);
        $normalized = $this->normalizer->normalize($resource);
        $this->assertArrayHasKey('availableTransitions', $normalized);
        $availableTransitions = $normalized['availableTransitions'];
        $this->assertCount(1, $availableTransitions);
        $this->assertEquals(['update'], EntityUtils::mapToIds($availableTransitions));
    }

    public function testGettingFullContent() {
        $resource = $this->createResourceMock(
            1,
            null,
            [SystemMetadata::RESOURCE_LABEL => 'ala', 2 => 'kot', SystemMetadata::VISIBILITY => [1]]
        );
        $normalized = $this->normalizer->normalize($resource);
        $this->assertArrayHasKey('isTeaser', $normalized);
        $this->assertFalse($normalized['isTeaser']);
        $this->assertArrayHasKey('canView', $normalized);
        $this->assertTrue($normalized['canView']);
        $this->assertArrayHasKey('contents', $normalized);
        $contents = $normalized['contents'];
        $this->assertCount(3, $contents);
        $this->assertEquals([['value' => 'ala']], $contents[SystemMetadata::RESOURCE_LABEL]);
        $this->assertEquals([['value' => 'kot']], $contents[2]);
        $this->assertEquals([['value' => 1]], $contents[SystemMetadata::VISIBILITY]);
    }

    public function testSetsCanViewToFalseIfFullNotGranted() {
        $this->securityOracle->method('hasMetadataPermission')->willReturn(false);
        $resource = $this->createResourceMock(
            1,
            null,
            [SystemMetadata::RESOURCE_LABEL => 'ala', 2 => 'kot']
        );
        $normalized = $this->normalizer->normalize($resource, null, [ResourceNormalizer::ALWAYS_RETURN_TEASER]);
        $this->assertArrayHasKey('isTeaser', $normalized);
        $this->assertArrayHasKey('canView', $normalized);
        $this->assertTrue($normalized['isTeaser']);
        $this->assertFalse($normalized['canView']);
    }

    public function testHasChildrenReturnTrueIfHasChildren() {
        $this->resourceRepository->method('hasChildren')->willReturn(true);
        $normalized = $this->normalizer->normalize($this->resourceWithChild);
        $hasChildren = $normalized['hasChildren'];
        $this->assertTrue($hasChildren);
    }

    public function testHasChildrenReturnFalseIfNoChildren() {
        $this->resourceRepository->method('hasChildren')->willReturn(false);
        $normalized = $this->normalizer->normalize($this->resource);
        $hasChildren = $normalized['hasChildren'];
        $this->assertFalse($hasChildren);
    }

    private function transition(string $id): ResourceWorkflowTransition {
        return new ResourceWorkflowTransition([], [], [], $id);
    }
}
