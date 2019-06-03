<?php
namespace Repeka\Tests\Integration\Security;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/** @small */
class EntityViewPermissionIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var SecurityOracle */
    private $oracle;
    /** @var User */
    private $noRolesUser;
    /** @var ResourceEntity */
    private $userRk;
    /** @var ResourceKind */
    private $groupRk;
    /** @var ResourceKind */
    private $universityRk;
    /** @var TokenInterface */
    private $noRolesUserToken;
    /** @var Metadata */
    private $usernameMetadata;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->oracle = $this->container->get(SecurityOracle::class);
        $this->noRolesUser = $this->getUserByUsername('user');
        $this->noRolesUserToken = $this->simulateAuthentication($this->noRolesUser);
        $this->userRk = $this->getResourceRepository()->findOne(SystemResourceKind::USER);
        $this->groupRk = $this->getResourceKindRepository()->findByName('user_group');
        $this->universityRk = $this->getResourceKindRepository()->findByName('university');
        $this->usernameMetadata = $this->findMetadataByName('username');
        $phpBook = $this->getPhpBookResource();
        $visibilityContents = $phpBook->getContents()->withMergedValues(
            SystemMetadata::VISIBILITY,
            $this->noRolesUser->getUserResourceId()
        );
        $this->handleCommandBypassingFirewall(new ResourceGodUpdateCommand($phpBook, $visibilityContents));
    }

    public function testNoRolesUserCannotViewResourceKindWithNoVisibleResources() {
        $this->assertFalse($this->oracle->hasViewPermission($this->userRk, $this->noRolesUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->groupRk, $this->noRolesUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->universityRk, $this->noRolesUserToken));
        $forbiddenBookRk = $this->getResourceKindRepository()->findByName('forbidden-book');
        $this->assertFalse($this->oracle->hasViewPermission($forbiddenBookRk, $this->noRolesUserToken));
    }

    public function testNoRolesUserCanViewResourceKindsWithAnyVisibleResources() {
        $bookRk = $this->getResourceKindRepository()->findByName('book');
        $this->assertTrue($this->oracle->hasViewPermission($bookRk, $this->noRolesUserToken));
    }

    public function testNoRolesUserCannotViewMetadataWithNoVisibleResources() {
        $this->assertFalse($this->oracle->hasViewPermission($this->usernameMetadata, $this->noRolesUserToken));
    }

    public function testNoRolesUserCanViewMetadataWithAnyVisibleResources() {
        $bookRk = $this->getResourceKindRepository()->findByName('book');
        $this->assertTrue($this->oracle->hasViewPermission($bookRk->getMetadataByIdOrName('tytuł'), $this->noRolesUserToken));
    }

    public function testNoRolesUserCannotViewWorkflowWithNoVisibleResources() {
        $this->assertFalse($this->oracle->hasViewPermission($this->userRk->getWorkflow(), $this->noRolesUserToken));
    }

    public function testNoRolesUserCanViewWorkflowWithAnyVisibleResources() {
        $bookRk = $this->getResourceKindRepository()->findByName('book');
        $this->assertTrue($this->oracle->hasViewPermission($bookRk->getWorkflow(), $this->noRolesUserToken));
    }
}
