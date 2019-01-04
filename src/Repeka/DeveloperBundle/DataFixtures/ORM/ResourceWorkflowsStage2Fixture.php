<?php
// @codingStandardsIgnoreFile
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;

class ResourceWorkflowsStage2Fixture extends RepekaFixture {
    const ORDER = ResourcesFixture::ORDER + 1;

    private $userGroupSignedId;
    private $visibilityMetadataId;
    private $userGroupAdminsId;

    /** @inheritdoc */
    public function load(ObjectManager $manager) {
        $this->userGroupSignedId = $this->getReference(ResourcesFixture::REFERENCE_USER_GROUP_SIGNED)->getId();
        $this->userGroupAdminsId = $this->getReference(ResourcesFixture::REFERENCE_USER_GROUP_ADMINS)->getId();
        $this->visibilityMetadataId = SystemMetadata::VISIBILITY;
        $this->updateBookWorkflow();
        $this->updateUserWorkfow();
        $this->addWorkflowToUserResourceKind($manager);
        $this->moveUsersToFirstPlaceInWorkflow($manager);
    }

    private function updateBookWorkflow() {
        /** @var ResourceWorkflow $bookWorkflow */
        $bookWorkflow = $this->getReference(ResourceWorkflowsFixture::BOOK_WORKFLOW);
        $userGroupScannersId = $this->getReference(ResourcesFixture::REFERENCE_USER_GROUP_SCANNERS)->getId();
        $titleMetadataId = $this->getReference(MetadataFixture::REFERENCE_METADATA_TITLE)->getId();
        $fileMetadata = $this->getReference(MetadataFixture::REFERENCE_METADATA_FILE);
        $fileMetadataId = $fileMetadata->getId();
        $scannerMetadata = $this->getReference(MetadataFixture::REFERENCE_METADATA_ASSIGNED_SCANNER);
        $realScannerMetadataId = $this->getReference(MetadataFixture::REFERENCE_METADATA_REAL_SCANNER)->getId();
        $scannerMetadataId = $scannerMetadata->getId();
        $supervisorMetadata = $this->getReference(MetadataFixture::REFERENCE_METADATA_SUPERVISOR);
        $supervisorMetadataId = $supervisorMetadata->getId();
        $creatorMetadata = $this->getReference(MetadataFixture::REFERENCE_METADATA_CREATOR);
        $creatorMetadataId = $creatorMetadata->getId();
        $creationDateMetadataId = $this->getReference(MetadataFixture::REFERENCE_METADATA_CREATION_DATE)->getId();
        $reproductorMetadataId = SystemMetadata::REPRODUCTOR;
        $places = json_decode(
            <<<JSON
[
  {"id": "y1oosxtgf", "label": {"PL": "Zaimportowana", "EN":"Imported"},                      "requiredMetadataIds": [$titleMetadataId], "lockedMetadataIds": [$realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId], "autoAssignMetadataIds": [$creatorMetadataId], "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "data_utworzenia_rekordu", "metadataValue": "{{ 'now'|date('Y-m-d H:i:s') }}", "setOnlyWhenEmpty": true}},{"name": "repekaMetadataValueSetter", "config": {"metadataName": "Nadzorujący", "metadataValue": "{% if 'OPERATOR-books' in command.executor.roles %}{{ command.executor.userData.id }}{% endif %}", "setOnlyWhenEmpty": true}}, {"name": "repekaMetadataValueSetter", "config": {"metadataName": $this->visibilityMetadataId, "metadataValue": $this->userGroupAdminsId, "setOnlyWhenEmpty": false}}, {"name": "repekaMetadataValueSetter", "config": {"metadataName": $this->visibilityMetadataId, "metadataValue": $userGroupScannersId, "setOnlyWhenEmpty": false}}]},
  {"id": "lb1ovdqcy", "label": {"PL": "Do skanowania", "EN":"Ready to scan"},                 "requiredMetadataIds": [$scannerMetadataId, $supervisorMetadataId], "lockedMetadataIds": [$realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId]},
  {"id": "qqd3yk499", "label": {"PL": "Zeskanowana", "EN":"Scanned"},                         "lockedMetadataIds": [$supervisorMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId], "assigneeMetadataIds": [$scannerMetadataId], "autoAssignMetadataIds": [$realScannerMetadataId]},
  {"id": "9qq9ipqa3", "label": {"PL": "Wymaga ponownego skanowania", "EN":"Require rescan"},  "lockedMetadataIds": [$scannerMetadataId, $supervisorMetadataId, $realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId]},
  {"id": "ss9qm7r78", "label": {"PL": "Zweryfikowana", "EN":"Verified"},                      "requiredMetadataIds": [$fileMetadataId], "lockedMetadataIds": [$scannerMetadataId, $supervisorMetadataId, $realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId]},
  {"id": "jvz160sl4", "label": {"PL": "Rozpoznana", "EN":"Recognized"},                       "lockedMetadataIds": [$fileMetadataId, $scannerMetadataId, $supervisorMetadataId, $realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId]},
  {"id": "xo77kutzk", "label": {"PL": "Zaakceptowana", "EN":"Accepted"},                      "lockedMetadataIds": [$fileMetadataId, $scannerMetadataId, $supervisorMetadataId, $realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId]},
  {"id": "j70hlpsvu", "label": {"PL": "Opublikowana", "EN":"Published"},                      "lockedMetadataIds": [$titleMetadataId, $fileMetadataId, $scannerMetadataId, $supervisorMetadataId, $realScannerMetadataId, $creationDateMetadataId, $reproductorMetadataId, $creatorMetadataId], "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "visibility", "metadataValue": [$this->userGroupSignedId], "setOnlyWhenEmpty": false}}]}
]
JSON
            ,
            true
        );
        $this->handleCommand(
            new ResourceWorkflowUpdateCommand(
                $bookWorkflow,
                $bookWorkflow->getName(),
                $places,
                $bookWorkflow->getTransitions(),
                $bookWorkflow->getDiagram(),
                $bookWorkflow->getThumbnail()
            )
        );
    }

    private function updateUserWorkfow() {
        /** @var ResourceWorkflow $userWorkflow */
        $userWorkflow = $this->getReference(ResourceWorkflowsFixture::USER_WORKFLOW);
        $groupMemberMetadataId = SystemMetadata::GROUP_MEMBER;
        $places = json_decode(
            <<<JSON
[{"id": "dw5kam1sr", "label": {"EN": "Signed up", "PL": "Zarejestrowany"}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": $this->visibilityMetadataId, "metadataValue": $this->userGroupAdminsId}}, {"name": "repekaMetadataValueSetter", "config": {"metadataName": $groupMemberMetadataId, "metadataValue": $this->userGroupSignedId}}], "lockedMetadataIds": [], "assigneeMetadataIds": [], "requiredMetadataIds": [-2], "autoAssignMetadataIds": []}]
JSON
            ,
            true
        );
        $this->handleCommand(
            new ResourceWorkflowUpdateCommand(
                $userWorkflow,
                $userWorkflow->getName(),
                $places,
                $userWorkflow->getTransitions(),
                $userWorkflow->getDiagram(),
                $userWorkflow->getThumbnail()
            )
        );
    }

    private function addWorkflowToUserResourceKind(ObjectManager $manager) {
        /** @var ResourceWorkflow $userWorkflow */
        $userWorkflow = $this->getReference(ResourceWorkflowsFixture::USER_WORKFLOW);
        /** @var ResourceKind $userResourceKind */
        $userResourceKind = $manager->getRepository(ResourceKind::class)->findOne(SystemResourceKind::USER);
        $this->handleCommand(
            new ResourceKindUpdateCommand(
                $userResourceKind,
                $userResourceKind->getLabel(),
                $userResourceKind->getMetadataList(),
                $userWorkflow
            )
        );
    }

    private function moveUsersToFirstPlaceInWorkflow(ObjectManager $manager) {
        /** @var User[] $users */
        $users = $manager->getRepository(UserEntity::class)->findAll();
        foreach ($users as $user) {
            /** @var ResourceWorkflowPlace $resourceWorkflowPlace */
            $resourceWorkflowPlace = $user->getUserData()->getKind()->getWorkflow()->getInitialPlace();
            $user->getUserData()->setMarking([$resourceWorkflowPlace->getId() => true]);
            $manager->getRepository(ResourceEntity::class)->save($user->getUserData());
        }
    }
}
