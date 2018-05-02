<?php
namespace Repeka\Tests\Application\Serialization;

use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResourceNormalizerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var TransitionPossibilityChecker|\PHPUnit_Framework_MockObject_MockObject */
    private $checker;

    /** @var ResourceNormalizer */
    private $normalizer;

    protected function setUp() {
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->resource->method('getWorkflow')->willReturn($this->workflow);
        // TokenStorage
        $user = $this->createMock(User::class);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $tokenStorage = $this->createMock(TokenStorage::class);
        $tokenStorage->method('getToken')->willReturn($token);
        // TransitionPossibilityChecker
        $this->checker = $this->createMock(TransitionPossibilityChecker::class);
        $displayStrategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        // test subject
        $this->normalizer = new ResourceNormalizer($tokenStorage, $this->checker, $displayStrategyEvaluator);
        $normalizerService = $this->createMock(NormalizerInterface::class);
        $normalizerService->method('normalize')->willReturnArgument(0);
        $this->normalizer->setNormalizer($normalizerService);
    }

    /** @SuppressWarnings("PHPMD.UnusedLocalVariable")  */
    public function testGettingBlockedTransitions() {
        $this->workflow->method('getTransitions')->willReturn([
            $this->transition('a'),
            $this->transition('b'),
            $this->transition('c'),
        ]);
        $this->checker->method('check')->willReturnCallback(function ($resource, ResourceWorkflowTransition $transition, $user) {
            return ($transition->getId() == 'a')
                ? new TransitionPossibilityCheckResult([], false, false)
                : new TransitionPossibilityCheckResult([], false, true);
        });
        $normalized = $this->normalizer->normalize($this->resource);
        $this->assertArrayHasKey('blockedTransitions', $normalized);
        $blockedTransitions = $normalized['blockedTransitions'];
        $this->assertCount(2, $blockedTransitions);
        $this->assertArrayHasKey('b', $blockedTransitions);
        $this->assertArrayHasKey('c', $blockedTransitions);
    }

    private function transition(string $id): ResourceWorkflowTransition {
        return new ResourceWorkflowTransition([], [], [], [], $id);
    }
}
