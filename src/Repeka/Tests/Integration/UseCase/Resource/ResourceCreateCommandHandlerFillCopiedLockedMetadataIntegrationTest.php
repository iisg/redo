<?php
namespace Repeka\Tests\Integration\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class ResourceCreateCommandHandlerFillCopiedLockedMetadataIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $category;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->loadCategory();
        $this->setCategoryReproductor(666);
    }

    /** @before */
    public function loadCategory() {
        $this->category = $this->findResourceByContents([$this->findMetadataByName('nazwa_kategorii')->getId() => 'E-booki']);
    }

    private function setCategoryReproductor($reproductorsIds) {
        $contents = $this->category->getContents()->withReplacedValues(SystemMetadata::REPRODUCTOR, $reproductorsIds);
        $this->handleCommandBypassingFirewall(new ResourceUpdateContentsCommand($this->category, $contents));
    }

    private function addCategorySubresource(array $additionalContents = [], ?User $executor = null): ResourceEntity {
        $command = new ResourceCreateCommand(
            $this->getPhpBookResource()->getKind(),
            ResourceContents::fromArray(
                array_replace(
                    [
                        $this->findMetadataByName('Tytuł')->getId() => 'Podzasób',
                        SystemMetadata::PARENT => $this->category->getId(),
                    ],
                    $additionalContents
                )
            ),
            $executor
        );
        return $this->handleCommandBypassingFirewall($command);
    }

    /** @small */
    public function testCopyingValueFromParentWhenTheMetadataIsLocked() {
        $resource = $this->addCategorySubresource();
        $this->assertEquals([666], $resource->getContents()->getValuesWithoutSubmetadata(SystemMetadata::REPRODUCTOR));
    }

    /** @small */
    public function testCanSetTheSameValueAsWouldBeCopiedFromParentWhenTheMetadataIsLocked() {
        $resource = $this->addCategorySubresource([SystemMetadata::REPRODUCTOR => 666]);
        $this->assertEquals([666], $resource->getContents()->getValuesWithoutSubmetadata(SystemMetadata::REPRODUCTOR));
    }

    /** @small */
    public function testDifferentValueIsReplacesWithCorrectOne() {
        $resource = $this->addCategorySubresource([SystemMetadata::REPRODUCTOR => 555]);
        $this->assertEquals([666], $resource->getContents()->getValuesWithoutSubmetadata(SystemMetadata::REPRODUCTOR));
    }

    /** @small */
    public function testCanAddIfReproductorIsTheSameAsWouldBeAutoAssigned() {
        $this->setCategoryReproductor($this->getAdminUser()->getUserData()->getId());
        $this->changeReproductorMetadataToAutoAssign();
        $resource = $this->addCategorySubresource([], $this->getAdminUser());
        $this->assertEquals(
            [$this->getAdminUser()->getUserData()->getId()],
            $resource->getContents()->getValuesWithoutSubmetadata(SystemMetadata::REPRODUCTOR)
        );
    }

    /** @small */
    public function testAutoAssignValueIsAssignedAndCopiedFromParent() {
        $firstAdminGroupId = $this->getAdminUser()->getUserGroupsIds()[0];
        $this->setCategoryReproductor($firstAdminGroupId);
        $this->changeReproductorMetadataToAutoAssign();
        $resource = $this->addCategorySubresource([], $this->getAdminUser());
        $this->assertEquals(
            [$firstAdminGroupId, $this->getAdminUser()->getUserData()->getId()],
            $resource->getContents()->getValuesWithoutSubmetadata(SystemMetadata::REPRODUCTOR)
        );
    }

    public function testCannotAddSubresourceIfAutoAssignReproductorButSomeOtherUserIsSet() {
        $this->expectException(InvalidCommandException::class);
        $this->changeReproductorMetadataToAutoAssign();
        $this->addCategorySubresource([], $this->getAdminUser());
    }

    private function changeReproductorMetadataToAutoAssign(): void {
        $workflow = $this->getPhpBookResource()->getWorkflow();
        $initialPlaceArray = $workflow->getInitialPlace()->toArray();
        $initialPlaceArray['autoAssignMetadataIds'][] = SystemMetadata::REPRODUCTOR;
        $initialPlace = ResourceWorkflowPlace::fromArray($initialPlaceArray);
        $places = $workflow->getPlaces();
        array_splice($places, 0, 1, [$initialPlace]);
        $this->handleCommandBypassingFirewall(
            new ResourceWorkflowUpdateCommand(
                $workflow,
                $workflow->getName(),
                $places,
                $workflow->getTransitions(),
                $workflow->getDiagram(),
                $workflow->getThumbnail()
            )
        );
    }
}
