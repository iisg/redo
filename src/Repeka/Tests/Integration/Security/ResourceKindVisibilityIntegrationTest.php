<?php
namespace Repeka\Tests\Integration\Security;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/** @small */
class ResourceKindVisibilityIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var SecurityOracle */
    private $oracle;
    /** @var User */
    private $testerUser;
    /** @var ResourceEntity */
    private $userRk;
    /** @var ResourceKind */
    private $groupRk;
    /** @var ResourceKind */
    private $universityRk;
    /** @var TokenInterface */
    private $testerUserToken;
    /** @var Metadata */
    private $usernameMetadata;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->oracle = $this->container->get(SecurityOracle::class);
        $this->testerUser = $this->getUserByName('tester');
        $this->testerUserToken = $this->simulateAuthentication($this->testerUser);
        $this->userRk = $this->getResourceRepository()->findOne(SystemResourceKind::USER);
        $this->groupRk = $this->getResourceKindRepository()->findByName('user_group');
        $this->universityRk = $this->getResourceKindRepository()->findByName('university');
        $this->usernameMetadata = $this->findMetadataByName('username');
    }

    public function testTesterCannotViewResourceKindsWithNoVisibleResources() {
        $this->assertFalse($this->oracle->hasViewPermission($this->userRk, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->groupRk, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->universityRk, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->userRk->getWorkflow(), $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->usernameMetadata, $this->testerUserToken));
    }

    public function testTesterCanViewResourceKindsWithAnyVisibleResources() {
        $bookRk = $this->getResourceKindRepository()->findByName('book');
        $forbiddenBookRk = $this->getResourceKindRepository()->findByName('forbidden-book');
        $this->assertTrue($this->oracle->hasViewPermission($bookRk, $this->testerUserToken));
        $this->assertTrue($this->oracle->hasViewPermission($forbiddenBookRk, $this->testerUserToken));
        $this->assertTrue($this->oracle->hasViewPermission($bookRk->getWorkflow(), $this->testerUserToken));
    }
}
