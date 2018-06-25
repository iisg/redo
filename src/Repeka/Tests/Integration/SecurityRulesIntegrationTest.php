<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class SecurityRulesIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $resourceKind;

    /** @before */
    public function loadFixtures() {
        $this->loadAllFixtures();
    }

    /** @before */
    public function createTestResourceKind() {
        $this->resourceKind = $this->createResourceKind(
            ['PL' => 'Test', 'EN' => 'Test'],
            [$this->findMetadataByName('Tytuł')]
        );
    }

    public function testAllowedTopLevelResourceCreationWhenResourceClassAdmin() {
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $resource = $this->handleCommandWithUserRoles(
            $command,
            [SystemRole::ADMIN()->roleName('books'), SystemRole::OPERATOR()->roleName('books')]
        );
        $this->assertNotNull($resource);
    }

    public function testForbiddenResourceCreationWhenNoRoles() {
        $this->expectException(InsufficientPrivilegesException::class);
        $this->expectExceptionMessage(SystemRole::ADMIN()->roleName('books'));
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->handleCommandWithUserRoles($command, []);
    }

    public function testForbiddenTopLevelResourceCreationWhenOnlyOperator() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->handleCommandWithUserRoles($command, [SystemRole::OPERATOR()->roleName('books')]);
    }

    public function testForbiddenTopLevelResourceCreationWhenAdminOfAnotherClass() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::empty());
        $this->handleCommandWithUserRoles($command, [SystemRole::ADMIN()->roleName('unicorns')]);
    }

    public function testAllowedFetchingResourceListForAnyOperator() {
        $command = ResourceListQuery::builder()->build();
        $this->handleCommandWithUserRoles($command, [SystemRole::OPERATOR()->roleName()]);
    }

    public function testAllowedResourceKindCreationWhenAdminOfResourceClass() {
        $metadataList = [['id' => $this->findMetadataByName('Opis')->getId()]];
        $command = new ResourceKindCreateCommand(['PL' => 'Test', 'EN' => 'Test'], $metadataList);
        $this->handleCommandWithUserRoles($command, [SystemRole::ADMIN()->roleName('books')]);
    }

    public function testForbiddenResourceKindCreationWhenOperatorOfResourceClass() {
        $this->expectException(InsufficientPrivilegesException::class);
        $metadataList = [['id' => $this->findMetadataByName('Opis')->getId()]];
        $command = new ResourceKindCreateCommand(['PL' => 'Test', 'EN' => 'Test'], $metadataList);
        $this->handleCommandWithUserRoles($command, [SystemRole::OPERATOR()->roleName('books')]);
    }

    public function testCannotAddSubresourceIfNoReproductorsSet() {
        $this->expectException(InsufficientPrivilegesException::class);
        $this->addSubBookToCategory();
    }

    public function testCanAddSubresourceIfReproductor() {
        $category = $this->findResourceByContents([$this->findMetadataByName('nazwa_kategorii')->getId() => 'E-booki']);
        $category->updateContents(
            $category->getContents()->withReplacedValues(SystemMetadata::REPRODUCTOR, $this->getAdminUser()->getUserData()->getId())
        );
        $this->getEntityManager()->persist($category);
        $this->resetEntityManager(ResourceRepository::class);
        $resource = $this->addSubBookToCategory();
        $this->assertNotNull($resource);
        $this->assertTrue($resource->hasParent());
        $this->assertEquals([$category->getId()], $resource->getValues(SystemMetadata::PARENT));
    }

    public function testCannotAddSubresourceIfSomeoneElseIsReproductor() {
        $this->expectException(InsufficientPrivilegesException::class);
        $category = $this->findResourceByContents([$this->findMetadataByName('nazwa_kategorii')->getId() => 'E-booki']);
        $category->updateContents($category->getContents()->withReplacedValues(SystemMetadata::REPRODUCTOR, 666));
        $this->getEntityManager()->persist($category);
        $this->resetEntityManager(ResourceRepository::class);
        $this->addSubBookToCategory();
    }

    private function addSubBookToCategory() {
        $category = $this->findResourceByContents([$this->findMetadataByName('nazwa_kategorii')->getId() => 'E-booki']);
        $command = new ResourceCreateCommand(
            $this->getPhpBookResource()->getKind(),
            ResourceContents::fromArray(
                [
                    $this->findMetadataByName('Tytuł')->getId() => 'Test',
                    SystemMetadata::PARENT => $category->getId(),
                ]
            )
        );
        return $this->handleCommandWithUserRoles($command, [SystemRole::OPERATOR()->roleName('books')]);
    }

    private function handleCommandWithUserRoles(Command $command, array $roles) {
        $admin = $this->getAdminUser();
        $admin->updateRoles($roles);
        $this->simulateAuthentication($admin);
        return $this->container->get(CommandBus::class)->handle($command);
    }
}
