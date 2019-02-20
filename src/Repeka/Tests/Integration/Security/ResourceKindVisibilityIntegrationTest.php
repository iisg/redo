<?php
namespace Repeka\Tests\Integration\Security;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
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
    private $ebooks;
    /** @var ResourceKind */
    private $categoryRk;
    /** @var ResourceKind */
    private $bookRk;
    /** @var TokenInterface */
    private $testerUserToken;
    /** @var Metadata */
    private $titleMetadata;
    /** @var Metadata */
    private $categoryNameMetadata;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->oracle = $this->container->get(SecurityOracle::class);
        $this->testerUser = $this->getUserByName('tester');
        $this->testerUserToken = $this->simulateAuthentication($this->testerUser);
        $this->ebooks = $this->findResourceByContents(['Nazwa kategorii' => 'E-booki']);
        $this->categoryRk = $this->ebooks->getKind();
        $this->bookRk = $this->getPhpBookResource()->getKind();
        $this->titleMetadata = $this->findMetadataByName('tytul');
        $this->categoryNameMetadata = $this->findMetadataByName('nazwa_kategorii');
    }

    public function testTesterCannotViewAnythingAtTheBeginning() {
        $this->assertFalse($this->oracle->hasViewPermission($this->bookRk, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->categoryRk, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->titleMetadata, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->categoryNameMetadata, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->bookRk->getWorkflow(), $this->testerUserToken));
    }

    public function testCanViewResourcesMetadataAndWorkflowsUserCanAdd() {
        $contents = $this->ebooks->getContents()->withReplacedValues(
            SystemMetadata::REPRODUCTOR,
            $this->testerUser->getUserData()->getId()
        );
        $this->handleCommandBypassingFirewall(new ResourceUpdateContentsCommand($this->ebooks, $contents));
        $this->assertTrue($this->oracle->hasViewPermission($this->bookRk, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->categoryRk, $this->testerUserToken));
        $this->assertTrue($this->oracle->hasViewPermission($this->titleMetadata, $this->testerUserToken));
        $this->assertFalse($this->oracle->hasViewPermission($this->categoryNameMetadata, $this->testerUserToken));
        $this->assertTrue($this->oracle->hasViewPermission($this->bookRk->getWorkflow(), $this->testerUserToken));
    }
}
