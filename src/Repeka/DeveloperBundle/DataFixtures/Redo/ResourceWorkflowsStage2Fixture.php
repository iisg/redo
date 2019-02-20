<?php
// @codingStandardsIgnoreFile
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Assert\Assertion;
use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Utils\StringUtils;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceWorkflowsStage2Fixture extends RepekaFixture {
    const ORDER = ResourcesFixture::ORDER + 1;

    /** @var MetadataRepository */
    private $metadataRepository;

    private $userGroupSignedId;
    private $visibilityMetadataId;
    private $userGroupAdminsId;

    /** @inheritdoc */
    public function load(ObjectManager $manager) {
        $this->metadataRepository = $manager->getRepository(Metadata::class);
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
        // @codingStandardsIgnoreStart
        // @formatter:off
        $places = json_decode(
            <<<JSON
[
  {"id": "y1oosxtgf", "label": {"PL": "Zaimportowana", "EN":"Imported"},                      {$this->createMetadataRequirements('books', ['tytul'], ['opis'], [], ['osoba_tworzaca_rekord'])}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "data_utworzenia_rekordu", "metadataValue": "{{ 'now'|date('Y-m-d H:i:s') }}", "setOnlyWhenEmpty": true}},{"name": "repekaMetadataValueSetter", "config": {"metadataName": "Nadzorujący", "metadataValue": "{% if 'OPERATOR-books' in command.executor.roles %}{{ command.executor.userData.id }}{% endif %}", "setOnlyWhenEmpty": true}}, {"name": "repekaMetadataValueSetter", "config": {"metadataName": $this->visibilityMetadataId, "metadataValue": $this->userGroupAdminsId, "setOnlyWhenEmpty": false}}, {"name": "repekaMetadataValueSetter", "config": {"metadataName": $this->visibilityMetadataId, "metadataValue": $userGroupScannersId, "setOnlyWhenEmpty": false}}]},
  {"id": "lb1ovdqcy", "label": {"PL": "Do skanowania", "EN":"Ready to scan"},                 {$this->createMetadataRequirements('books', ['skanista', 'Nadzorujący'])}},
  {"id": "qqd3yk499", "label": {"PL": "Zeskanowana", "EN":"Scanned"},                         {$this->createMetadataRequirements('books', [], ['okladka'], ['skanista'], ['Zeskanowane przez'])}},
  {"id": "9qq9ipqa3", "label": {"PL": "Wymaga ponownego skanowania", "EN":"Require rescan"},  {$this->createMetadataRequirements('books')}},
  {"id": "ss9qm7r78", "label": {"PL": "Zweryfikowana", "EN":"Verified"},                      {$this->createMetadataRequirements('books', ['okladka'])}},
  {"id": "jvz160sl4", "label": {"PL": "Rozpoznana", "EN":"Recognized"},                       {$this->createMetadataRequirements('books')}},
  {"id": "xo77kutzk", "label": {"PL": "Zaakceptowana", "EN":"Accepted"},                      {$this->createMetadataRequirements('books')}},
  {"id": "j70hlpsvu", "label": {"PL": "Opublikowana", "EN":"Published"},                      {$this->createMetadataRequirements('books')}, "pluginsConfig": [{"name": "repekaMetadataValueSetter", "config": {"metadataName": "visibility", "metadataValue": [$this->userGroupSignedId], "setOnlyWhenEmpty": false}}]},
  {"id": "xydjh1208", "label": {"PL": "Początek deponowania", "EN":"Deposit start"},          {$this->createMetadataRequirements('books')}, "pluginsConfig":[{"name":"workflowPlaceTagger","config":{"tagName":"deposit","tagValue":"start"}}]},
  {"id": "fj3873nck", "label": {"PL": "Dane o zasobie", "EN":"Resource data"},                {$this->createMetadataRequirements('books', ['tytul'], ['opis', 'wydawnictwo'], [], ['osoba_tworzaca_rekord'])}},
  {"id": "uu3kdj377", "label": {"PL": "Dodanie pliku", "EN":"File"},                          {$this->createMetadataRequirements('books', ['okladka'])}},
  {"id": "di3kd8267", "label": {"PL": "Podsumowanie", "EN":"Summary"},                        {$this->createMetadataRequirements('books')}, "pluginsConfig":[{"name":"workflowPlaceTagger","config":{"tagName":"deposit","tagValue":"end"}}]}
]
JSON
            ,
            true
        );
        $transitions = json_decode(
            <<<TRANSITIONS
[
    {"id":"e7d756ed-d6b3-4f2f-9517-679311e88b17","label":{"PL":"Do\u0142\u0105cz metryczk\u0119","EN":"Attach metrics"},"froms":["y1oosxtgf"],"tos":["lb1ovdqcy"]},
    {"id":"d3f73249-d10f-4d4b-8b63-be60b4c02081","label":{"PL":"Skanuj","EN":"Scan"},"froms":["lb1ovdqcy"],"tos":["qqd3yk499"]},
    {"id":"b2725b84-c470-40f7-b7b5-3850e0f2754c","label":{"PL":"Odrzu\u0107","EN":"Reject"},"froms":["qqd3yk499"],"tos":["9qq9ipqa3"]},
    {"id":"9faac2d6-3a58-4ead-9aa2-9181c778a2e7","label":{"PL":"Skanuj ponownie","EN":"Rescan"},"froms":["9qq9ipqa3"],"tos":["qqd3yk499"]},
    {"id":"1b59e8f1-26e9-4018-a6cf-a39ef8e8521b","label":{"PL":"Zweryfikuj","EN":"Verify"},"froms":["qqd3yk499"],"tos":["ss9qm7r78"]},
    {"id":"4d96170b-f486-443d-ad0c-7e882487f5e1","label":{"PL":"Rozpoznaj","EN":"Recognize"},"froms":["ss9qm7r78"],"tos":["jvz160sl4"]},
    {"id":"e603b0a3-d04f-495c-8caa-a67e604e3c87","label":{"PL":"Zaakceptuj","EN":"Accept"},"froms":["jvz160sl4"],"tos":["xo77kutzk"]},
    {"id":"ce30b481-8dde-40e4-ab7c-0bc90e918431","label":{"PL":"Opublikuj","EN":"Publish"},"froms":["xo77kutzk"],"tos":["j70hlpsvu"]},
    {"id":"83c98637-6173-40b2-8840-cc2ae914bcc4","label":{"PL":"Zdejmij","EN":"Unpublish"},"froms":["j70hlpsvu"],"tos":["xo77kutzk"]},
    {"id":"76cd52c5-6173-40b2-8840-cc2ae914bcc4","label":{"PL":"Uzupełnij szczegółowe informacje o zasobie","EN":"Start"},"froms":["xydjh1208"],"tos":["fj3873nck"]},
    {"id":"372cc532-6173-40b2-8840-cc2ae914bcc4","label":{"PL":"Dodaj plik zasobu","EN":"Next"},"froms":["fj3873nck"],"tos":["uu3kdj377"]},
    {"id":"88d736cc-6173-40b2-8840-cc2ae914bcc4","label":{"PL":"Podsumowanie informacji o zasobie","EN":"Next"},"froms":["uu3kdj377"],"tos":["di3kd8267"]},
    {"id":"323436cc-6173-40b2-8840-cc2ae914bcc4","label":{"PL":"Zakończ","EN":"Finish"},"froms":["di3kd8267"],"tos":["qqd3yk499"]}
]
TRANSITIONS
            ,
            true
        );
        // @formatter:on
        // @codingStandardsIgnoreEnd
        $this->handleCommand(
            new ResourceWorkflowUpdateCommand(
                $bookWorkflow,
                $bookWorkflow->getName(),
                $places,
                $transitions,
                '{"y1oosxtgf":{"x":51.01815994306967,"y":175.384765625},"lb1ovdqcy":{"x":252.21875,"y":175.60595703125},"qqd3yk499":{"x":266.026612773572,"y":59.700392994713134},"9qq9ipqa3":{"x":44.82338335188286,"y":70.47171223529797},"ss9qm7r78":{"x":405.75008979779426,"y":58.87617816576146},"jvz160sl4":{"x":558.795899588251,"y":55.28335247948525},"xo77kutzk":{"x":554.1022011563498,"y":203.39161358806604},"j70hlpsvu":{"x":378.4111711160704,"y":135.956784665596},"xydjh1208":{"x":36.645555374769906,"y":-1.16335096427841},"fj3873nck":{"x":105.45425030018411,"y":-43.34463909250698},"uu3kdj377":{"x":261.181018943651,"y":-10.273686498818162},"di3kd8267":{"x":552.5917080322446,"y":-12.796860607062506}}',
                'data:image/png;base64,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'
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

    private function createMetadataRequirements(
        string $resourceClass,
        array $requiredMetadata = [],
        array $optionalMetadata = [],
        array $assigneeMetadata = [],
        array $autoAssignMetadata = []
    ) {
        $query = MetadataListQuery::builder()
            ->filterByResourceClass($resourceClass)
            ->addSystemMetadataIds([SystemMetadata::REPRODUCTOR, SystemMetadata::VISIBILITY, SystemMetadata::TEASER_VISIBILITY])
            ->build();
        $metadataList = [];
        foreach ($this->metadataRepository->findByQuery($query) as $metadata) {
            $metadataList[$metadata->getName()] = $metadata;
        }
        $mapByName = function (string $name) use (&$metadataList) {
            $key = StringUtils::normalizeEntityName($name);
            Assertion::keyExists($metadataList, $key);
            $metadata = $metadataList[$key];
            unset($metadataList[$key]);
            return $metadata;
        };
        array_map($mapByName, $optionalMetadata);
        $requirements = [
            'requiredMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $requiredMetadata)),
            'assigneeMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $assigneeMetadata)),
            'autoAssignMetadataIds' => EntityUtils::mapToIds(array_map($mapByName, $autoAssignMetadata)),
        ];
        $requirements['lockedMetadataIds'] = EntityUtils::mapToIds($metadataList);
        return trim(json_encode($requirements), '{}');
    }
}
