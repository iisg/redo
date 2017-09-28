<?php
namespace Repeka\Tests\Domain\Entity\Workflow;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;

class ResourceWorkflowTransitionTest extends \PHPUnit_Framework_TestCase {
    public function testCannotApplyIfNoRolesExplicitlyPermitted() {
        $transition = new ResourceWorkflowTransition([], [], []);
        $user = $this->createMock(User::class);
        $this->assertFalse($transition->userHasRoleRequiredToApply($user));
    }
}
