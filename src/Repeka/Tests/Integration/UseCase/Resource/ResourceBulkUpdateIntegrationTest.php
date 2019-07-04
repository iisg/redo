<?php
namespace Repeka\Tests\Integration\UseCase\Resource;

use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Factory\BulkChanges\PendingUpdates;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\PrintableArray;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ResourceBulkUpdateIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    private const BULK_UPDATE_ENDPOINT = 'api/resources';

    /** @var ResourceKind */
    private $bookKind;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->bookKind = $this->getResourceKindRepository()->findByName('book');
    }

    public function testAddingContentsUpdate() {
        $displayStrategy = "{{ r|mTytul }}";
        $metadata = $this->getMetadataRepository()->findByName('opis');
        $change = [
            'change' => [
                'metadataId' => $metadata->getId(),
                'displayStrategy' => $displayStrategy,
                'addValuesAtBeginning' => false,
            ],
            'action' => UpdateType::APPEND,
        ];
        $this->assertStatusCode(202, $this->addPendingUpdateToAllBooks($change));
        $updatedResources = $this->getAllBooks();
        /** @var ResourceEntity $resource */
        foreach ($updatedResources as $resource) {
            $update = $resource->getPendingUpdates()->shiftUpdate();
            $this->assertNotNull($update);
            $this->assertEquals($change, $update);
        }
    }

    public function testAddingContentsUpdateToOneBook() {
        $displayStrategy = "{{ r|mTytul }}";
        $metadata = $this->getMetadataRepository()->findByName('opis');
        $change = [
            'change' => [
                'metadataId' => $metadata->getId(),
                'displayStrategy' => $displayStrategy,
                'addValuesAtBeginning' => false,
            ],
            'action' => UpdateType::APPEND,
        ];
        $filters = ['contentsFilter' => ['tytul' => 'leczyć']];
        $this->assertStatusCode(202, $this->addPendingUpdateToAllBooks($change, $filters, 1));
        $phpBook = $this->getPhpBookResource();
        $this->assertCount(1, $phpBook->getPendingUpdates());
        $otherBooks = $this->getAllBooks();
        foreach ($otherBooks as $resource) {
            if ($resource->getId() != $phpBook->getId()) {
                $this->assertEmpty($resource->getPendingUpdates());
            }
        }
    }

    public function testAddingTransitionUpdate() {
        $change = [
            'change' => ['transitionId' => 'somewhere_else'],
            'action' => UpdateType::EXECUTE_TRANSITION,
        ];
        $this->assertEquals(202, $this->addPendingUpdateToAllBooks($change)->getStatusCode());
        $updatedResources = $this->getAllBooks();
        $expected = array_merge_recursive($change, ['change' => ['executorId' => $this->getAdminUser()->getId()]]);
        /** @var ResourceEntity $resource */
        foreach ($updatedResources as $resource) {
            $update = $resource->getPendingUpdates()->shiftUpdate();
            $this->assertNotNull($update);
            $this->assertEquals($expected, $update);
        }
    }

    public function testAddingMoveToPlaceUpdate() {
        $change = [
            'change' => ['placeId' => 'somewhere_else'],
            'action' => UpdateType::MOVE_TO_PLACE,
        ];
        $this->assertEquals(202, $this->addPendingUpdateToAllBooks($change)->getStatusCode());
        $updatedResources = $this->getAllBooks();
        /** @var ResourceEntity $resource */
        foreach ($updatedResources as $resource) {
            $update = $resource->getPendingUpdates()->shiftUpdate();
            $this->assertNotNull($update);
            $this->assertEquals($change, $update);
        }
    }

    public function testPluginsFiredAfterFullResourceTransition() {
        $resourceKind = $this->createTestResourceKind();
        $change = [
            'change' => ['transitionId' => 'fromStartToEnd', 'executorId' => $this->getAdminUser()->getId()],
            'action' => UpdateType::EXECUTE_TRANSITION,
        ];
        $resource = $this->createResource($resourceKind, [$resourceKind->getMetadataByName('m1')->getId() => 'value2']);
        $resource->setMarking(['start' => true]);
        $updates = $resource->getPendingUpdates()->addUpdate($change);
        $this->updateResourceAndTestContents($resource, $updates, $resource->getKind()->getMetadataByName('m3'), ['mvs was here']);
    }

    public function testTransitionImpossibleWithEmptyRequiredMetadata() {
        $resourceKind = $this->createTestResourceKind();
        $change = [
            'change' => ['transitionId' => 'fromStartToEnd', 'executorId' => $this->getAdminUser()->getId()],
            'action' => UpdateType::EXECUTE_TRANSITION,
        ];
        $resource = $this->createResource($resourceKind, []);
        $resource->setMarking(['start' => true]);
        $currentPlace = $resource->getCurrentPlace();
        $updates = $resource->getPendingUpdates()->addUpdate($change);
        $resource->setPendingUpdates($updates);
        $this->getResourceRepository()->save($resource);
        $this->getEntityManager()->flush();
        $this->executeCommand('repeka:resources-bulk-update');
        $this->assertEquals($currentPlace, $this->getResourceRepository()->findOne($resource->getId())->getCurrentPlace());
        /** @var AuditEntry $auditEntry */
        $auditEntry = $this->container->get(AuditEntryRepository::class)->findByQuery(
            AuditEntryListQuery::builder()->filterByCommandNames(['resources_bulk_update'])->build()
        )->getResults()[0];
        $this->assertEquals($resource->getId(), $auditEntry->getData()['resourceId']);
        $this->assertEquals($updates->shiftUpdate(), $auditEntry->getData()['update']);
        $this->assertFalse($auditEntry->isSuccessful());
    }

    public function testAddingRequiredMetadataValuesAsPendingUpdatesBeforeTransition() {
        $resourceKind = $this->createTestResourceKind();
        $contentsChange = [
            'change' => ['displayStrategy' => 'value', 'metadataId' => $resourceKind->getMetadataList()[0]->getId()],
            'action' => UpdateType::OVERRIDE,
        ];
        $transitionChange = [
            'change' => ['transitionId' => 'fromStartToEnd', 'executorId' => $this->getAdminUser()->getId()],
            'action' => UpdateType::EXECUTE_TRANSITION,
        ];
        $resource = $this->createResource($resourceKind, []);
        $resource->setMarking(['start' => true]);
        $updates = $resource->getPendingUpdates()
            ->addUpdate($contentsChange)
            ->addUpdate($transitionChange);
        $this->updateResourceAndTestContents($resource, $updates, $resource->getKind()->getMetadataByName('m3'), ['mvs was here']);
    }

    public function testCorrectUpdatesExecutedOnOtherUpdateError() {
        $phpBook = $this->getPhpBookResource();
        $currentPlace = $phpBook->getCurrentPlace();
        $descriptionMetadata = $phpBook->getKind()->getMetadataByName('opis');
        $transitionChange = [
            'change' => ['transitionId' => 'invalid', 'executorId' => $this->getAdminUser()->getId()],
            'action' => UpdateType::EXECUTE_TRANSITION,
        ];
        $updates = $phpBook->getPendingUpdates()->addUpdate($transitionChange);
        $phpBook->setPendingUpdates($updates);
        $this->getResourceRepository()->save($phpBook);
        $this->getEntityManager()->flush();
        $contentsChange = [
            'change' => ['displayStrategy' => 'new value', 'metadataId' => $descriptionMetadata->getId()],
            'action' => UpdateType::OVERRIDE,
        ];
        $this->assertEquals(202, $this->addPendingUpdateToAllBooks($contentsChange)->getStatusCode());
        $this->getEntityManager()->clear();
        $this->executeCommand('repeka:resources-bulk-update');
        $updatedResources = $this->getAllBooks();
        foreach ($updatedResources as $resource) {
            $this->assertEmpty($resource->getPendingUpdates());
            $this->assertEquals('new value', $resource->getValuesWithoutSubmetadata($descriptionMetadata)[0]);
        }
        $this->assertEquals($currentPlace, $this->getPhpBookResource()->getCurrentPlace());
        /** @var AuditEntry $auditEntry */
        $auditEntry = $this->container->get(AuditEntryRepository::class)->findByQuery(
            AuditEntryListQuery::builder()->filterByCommandNames(['resources_bulk_update'])->build()
        )->getResults()[0];
        $this->assertEquals($phpBook->getId(), $auditEntry->getData()['resourceId']);
    }

    public function testContentsUpdateForAllBooks() {
        $descriptionMetadata = $this->getMetadataRepository()->findByName('opis');
        $displayStrategy = "{{ r | mTytul | raw }}";
        $contentsChange = [
            'change' => ['displayStrategy' => $displayStrategy, 'metadataId' => $descriptionMetadata->getId()],
            'action' => UpdateType::OVERRIDE,
        ];
        $this->addPendingUpdateToAllBooks($contentsChange);
        $this->executeCommand("repeka:resources-bulk-update");
        $updatedBooks = $this->getAllBooks();
        $titleMetadata = $this->getMetadataRepository()->findByName('tytul');
        foreach ($updatedBooks as $book) {
            $expected = '' . new PrintableArray($book->getValues($titleMetadata));
            $this->assertEquals($expected, $book->getValuesWithoutSubmetadata($descriptionMetadata)[0]);
            $this->assertEmpty($book->getPendingUpdates());
        }
    }

    public function testRerenderDynamicMetadataForAllBooks() {
        $contentsChange = ['change' => [], 'action' => UpdateType::RERENDER_DYNAMIC_METADATA];
        $this->addPendingUpdateToAllBooks($contentsChange);
        $this->executeCommand("repeka:resources-bulk-update");
        $updatedBooks = $this->getAllBooks();
        foreach ($updatedBooks as $book) {
            $this->assertTrue($book->isDisplayStrategiesDirty());
        }
    }

    public function testApplyingForInvalidAction() {
        $contentsChange = ['change' => [], 'action' => 'unicorn'];
        $response = $this->addPendingUpdateToAllBooks($contentsChange);
        $this->assertStatusCode(400, $response);
    }

    public function testMovingAllBooksToScannedPlace() {
        $scannedPlace = $this->getPhpBookResource()->getWorkflow()->getPlaces()[2];
        $placeChange = [
            'change' => ['placeId' => $scannedPlace->getId()],
            'action' => UpdateType::MOVE_TO_PLACE,
        ];
        $this->addPendingUpdateToAllBooks($placeChange);
        $this->executeCommand("repeka:resources-bulk-update");
        $updatedBooks = $this->getAllBooks();
        foreach ($updatedBooks as $book) {
            $this->assertEmpty($book->getPendingUpdates());
            $this->assertEquals($scannedPlace, $book->getCurrentPlace());
        }
    }

    public function testMovingAllBooksToWrongPlace() {
        $placeChange = [
            'change' => ['placeId' => 'unicorn'],
            'action' => UpdateType::MOVE_TO_PLACE,
        ];
        $this->addPendingUpdateToAllBooks($placeChange);
        $this->executeCommand("repeka:resources-bulk-update");
        $updatedBooks = $this->getAllBooks();
        foreach ($updatedBooks as $book) {
            $this->assertEmpty($book->getPendingUpdates());
            $this->assertNotEquals('unicorn', $book->getCurrentPlace());
        }
    }

    public function testUpdateQueueOrder() {
        $descriptionMetadata = $this->getMetadataRepository()->findByName('opis');
        $phpBook = $this->getPhpBookResource();
        $metadataId = $descriptionMetadata->getId();
        $updates = $phpBook->getPendingUpdates()
            ->addUpdate(['change' => ['displayStrategy' => 'first', 'metadataId' => $metadataId], 'action' => UpdateType::OVERRIDE])
            ->addUpdate(['change' => ['displayStrategy' => 'second', 'metadataId' => $metadataId], 'action' => UpdateType::OVERRIDE])
            ->addUpdate(['change' => ['displayStrategy' => 'third', 'metadataId' => $metadataId], 'action' => UpdateType::OVERRIDE]);
        $phpBook->setPendingUpdates($updates);
        $this->getResourceRepository()->save($phpBook);
        $this->getEntityManager()->flush();
        $this->executeCommand("repeka:resources-bulk-update");
        $phpBook = $this->getPhpBookResource();
        $this->assertEmpty($phpBook->getPendingUpdates());
        $descriptionValues = $phpBook->getValuesWithoutSubmetadata($descriptionMetadata);
        $this->assertCount(1, $descriptionValues);
        $this->assertEquals('third', $descriptionValues[0]);
    }

    /** @small */
    public function testAddingNewValuesAtBeginning() {
        $metadata = $this->createMetadata(
            'begin',
            ['PL' => 'test', 'EN' => 'test'],
            [],
            [],
            MetadataControl::TEXT,
            'books',
            ['addValuesAtBeginning' => true]
        );
        $rk = $this->createResourceKind('test_kind', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => 'value']);
        $update = [
            'change' => ['displayStrategy' => 'first', 'metadataId' => $metadata->getId(), 'addValuesAtBeginning' => true],
            'action' => UpdateType::APPEND,
        ];
        $updates = $resource->getPendingUpdates()
            ->addUpdate($update)
            ->addUpdate(array_replace_recursive($update, ['change' => ['displayStrategy' => 'second']]))
            ->addUpdate(array_replace_recursive($update, ['change' => ['displayStrategy' => 'third']]));
        $this->updateResourceAndTestContents($resource, $updates, $metadata, ['third', 'second', 'first', 'value']);
    }

    /** @small */
    public function testAppendingValuesToTheEnd() {
        $metadata = $this->createMetadata(
            'end',
            ['PL' => 'test', 'EN' => 'test'],
            [],
            [],
            MetadataControl::TEXT,
            'books',
            ['addValuesAtBeginning' => false]
        );
        $update = [
            'change' => ['displayStrategy' => 'first', 'metadataId' => $metadata->getId(), 'addValuesAtBeginning' => false],
            'action' => UpdateType::APPEND,
        ];
        $rk = $this->createResourceKind('end_kind', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => 'value']);
        $updates = $resource->getPendingUpdates()->addUpdate($update)
            ->addUpdate(array_replace_recursive($update, ['change' => ['displayStrategy' => 'second']]))
            ->addUpdate(array_replace_recursive($update, ['change' => ['displayStrategy' => 'third']]));
        $this->updateResourceAndTestContents($resource, $updates, $metadata, ['value', 'first', 'second', 'third']);
    }

    /** @small */
    public function testAppendingToTheEndIsDefault() {
        $metadata = $this->createMetadata('no_order', ['PL' => 'test', 'EN' => 'test']);
        $rk = $this->createResourceKind('no_order_kind', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => 'value']);
        $updates = $resource->getPendingUpdates()
            ->addUpdate(['change' => ['displayStrategy' => 'first', 'metadataId' => $metadata->getId()], 'action' => UpdateType::APPEND])
            ->addUpdate(['change' => ['displayStrategy' => 'second', 'metadataId' => $metadata->getId()], 'action' => UpdateType::APPEND]);
        $this->updateResourceAndTestContents($resource, $updates, $metadata, ['value', 'first', 'second']);
    }

    public function testReplacingValues() {
        $phpBook = $this->getPhpBookResource();
        $titleMetadata = $phpBook->getKind()->getMetadataByName('tytul');
        $value = "It's not php anymore";
        $updates = $phpBook->getPendingUpdates()
            ->addUpdate(
                ['change' => ['displayStrategy' => $value, 'metadataId' => $titleMetadata->getId()], 'action' => UpdateType::OVERRIDE]
            );
        $this->updateResourceAndTestContents($phpBook, $updates, $titleMetadata, [$value]);
    }

    public function testRemovingValues() {
        $phpBook = $this->getPhpBookResource();
        $titleMetadata = $phpBook->getKind()->getMetadataByName('tytul');
        $updates = $phpBook->getPendingUpdates()
            ->addUpdate(
                ['change' => ['displayStrategy' => '[]', 'metadataId' => $titleMetadata->getId()], 'action' => UpdateType::OVERRIDE]
            );
        $this->updateResourceAndTestContents($phpBook, $updates, $titleMetadata, []);
    }

    public function testAppendingEmptyValueNotPossible() {
        $phpBook = $this->getPhpBookResource();
        $titleMetadata = $phpBook->getKind()->getMetadataByName('tytul');
        $updates = $phpBook->getPendingUpdates()
            ->addUpdate(
                ['change' => ['displayStrategy' => '{{null}}', 'metadataId' => $titleMetadata->getId()], 'action' => UpdateType::APPEND]
            );
        $expected = $phpBook->getValuesWithoutSubmetadata($titleMetadata);
        $this->updateResourceAndTestContents($phpBook, $updates, $titleMetadata, $expected);
    }

    /** @small */
    public function testValuesAreNotDuplicated() {
        $metadata = $this->createMetadata('test', ['PL' => 'test', 'EN' => 'test']);
        $rk = $this->createResourceKind('test_kind', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => 'one']);
        $updates = $resource->getPendingUpdates()
            ->addUpdate(['change' => ['displayStrategy' => 'one', 'metadataId' => $metadata->getId()], 'action' => UpdateType::APPEND])
            ->addUpdate(['change' => ['displayStrategy' => 'two', 'metadataId' => $metadata->getId()], 'action' => UpdateType::APPEND]);
        $this->updateResourceAndTestContents($resource, $updates, $metadata, ['one', 'two']);
    }

    /** @small */
    public function testReducingValues() {
        $metadata = $this->createMetadata('reduce', ['PL' => 'test', 'EN' => 'test']);
        $rk = $this->createResourceKind('reduce_kind', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => ['one', 'two', 'three']]);
        $updates = $resource->getPendingUpdates()
            ->addUpdate(['change' => ['displayStrategy' => 'three', 'metadataId' => $metadata->getId()], 'action' => UpdateType::OVERRIDE]);
        $this->updateResourceAndTestContents($resource, $updates, $metadata, ['three']);
    }

    /** @small */
    public function testAppendingJsonContent() {
        $metadata = $this->createMetadata('json', ['PL' => 'test', 'EN' => 'test']);
        $rk = $this->createResourceKind('json_kind', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => 'ala']);
        $template = '["ma", "kota"]';
        $updates = $resource->getPendingUpdates()
            ->addUpdate(['change' => ['displayStrategy' => $template, 'metadataId' => $metadata->getId()], 'action' => UpdateType::APPEND]);
        $this->updateResourceAndTestContents($resource, $updates, $metadata, ['ala', 'ma', 'kota']);
    }

    /** @small */
    public function testAppendingNonExistingMetadataImpossible() {
        $metadata = $this->createMetadata('valid', ['PL' => 'test', 'EN' => 'test']);
        $invalidMetadata = $this->createMetadata('invalid', ['PL' => 'test', 'EN' => 'test']);
        $rk = $this->createResourceKind('rk', ['PL' => 'test', 'EN' => 'test'], [$metadata]);
        $resource = $this->createResource($rk, [$metadata->getId() => 'ala']);
        $update = ['change' => ['displayStrategy' => 'value', 'metadataId' => $invalidMetadata->getId()], 'action' => UpdateType::APPEND];
        $updates = $resource->getPendingUpdates()->addUpdate($update);
        $this->updateResourceAndTestContents($resource, $updates, $invalidMetadata, []);
    }

    private function updateResourceAndTestContents(ResourceEntity $resource, PendingUpdates $updates, Metadata $metadata, $expectedValue) {
        $resource->setPendingUpdates($updates);
        $this->getResourceRepository()->save($resource);
        $this->getEntityManager()->flush();
        $this->executeCommand("repeka:resources-bulk-update");
        $updatedResource = $this->getResourceRepository()->findOne($resource->getId());
        $this->assertEmpty($updatedResource->getPendingUpdates());
        $this->assertEquals($expectedValue, $updatedResource->getValuesWithoutSubmetadata($metadata));
    }

    private function addPendingUpdateToAllBooks(array $change, array $additionalFilters = [], int $totalCount = 5) {
        $content = array_merge(['resourceClass' => 'books', 'totalCount' => $totalCount], $change);
        $params = array_merge(
            ['resourceClass' => 'books', 'resourceKinds' => [$this->getPhpBookResource()->getKind()->getId()]],
            $additionalFilters
        );
        $client = $this->createAdminClient();
        $this->setTestContainer($client);
        $query = http_build_query($params);
        $client->apiRequest("PUT", self::BULK_UPDATE_ENDPOINT . '?' . $query, $content);
        $this->getEntityManager()->clear();
        return $client->getResponse();
    }

    /** @return ResourceEntity[] */
    private function getAllBooks(): array {
        $this->getEntityManager()->clear();
        $listQuery = ResourceListQuery::builder()
            ->filterByResourceKind($this->bookKind)
            ->build();
        return $this->getResourceRepository()->findByQuery($listQuery)->getResults();
    }

    private function createTestResourceKind(): ResourceKind {
        $metadataList = [$this->createMetadata('m1'), $this->createMetadata('m2'), $this->createMetadata('m3')];
        $mvsConfig = ['metadataName' => 'm3', 'metadataValue' => 'mvs was here'];
        $pluginsConfig = ['name' => 'repekaMetadataValueSetter', 'config' => $mvsConfig];
        $startPlace = new ResourceWorkflowPlace(['PL' => 'start', 'EN' => 'start'], 'start');
        $endPlace = new ResourceWorkflowPlace(
            ['PL' => 'end', 'EN' => 'end'],
            'end',
            [$metadataList[0]->getId()],
            [$metadataList[1]->getId()],
            [],
            [],
            [$pluginsConfig]
        );
        $transition = new ResourceWorkflowTransition(
            ['PL' => 'fromStartToEnd', 'EN' => 'fromStartToEnd'],
            ['start'],
            ['end'],
            'fromStartToEnd'
        );
        $workflow = $this->createWorkflow(['PL' => 'test', 'EN' => 'test'], 'books', [$startPlace, $endPlace], [$transition]);
        return $this->createResourceKind('test_rk', ['PL' => 'test', 'EN' => 'test'], $metadataList, true, $workflow);
    }
}
