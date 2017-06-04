<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;

class ResourceWorkflowTransitionTest extends \PHPUnit_Framework_TestCase {
    public function testCannotApplyIfNoRolesExplicitlyPermitted() {
        $transition = new ResourceWorkflowTransition([], [], []);
        $user = $this->createMock(User::class);
        $this->assertFalse($transition->userHasRoleRequiredToApply($user));
    }
}
