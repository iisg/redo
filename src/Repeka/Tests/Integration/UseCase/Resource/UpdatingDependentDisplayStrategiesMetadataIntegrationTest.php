<?php
namespace Repeka\Tests\Integration\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class UpdatingDependentDisplayStrategiesMetadataIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $phpBook;
    /** @var ResourceEntity */
    private $scannerData;
    /** @var Metadata */
    private $scannerUsernameMetadata;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->phpBook = $this->getPhpBookResource();
        $this->scannerData = $this->findResourceByContents([SystemMetadata::USERNAME => 'skaner']);
        $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->phpBook));
        $this->scannerUsernameMetadata = $this->findMetadataByName('nazwaSkanisty');
    }

    private function changeScannerUsernameToNewScanner(): void {
        $this->assertEquals('skaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
        $this->handleCommandBypassingFirewall(
            new ResourceUpdateContentsCommand(
                $this->scannerData,
                $this->scannerData->getContents()->withReplacedValues(SystemMetadata::USERNAME, 'nowyskaner')
            )
        );
    }

    public function testUpdatingScannerUsername() {
        $this->changeScannerUsernameToNewScanner();
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertEquals('nowyskaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
    }

    public function testUpdatingScannerUsernameWhenScannerChanges() {
        $this->handleCommandBypassingFirewall(
            new ResourceUpdateContentsCommand(
                $this->phpBook,
                $this->phpBook->getContents()->withReplacedValues($this->findMetadataByName('skanista'), 1)
            )
        );
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertEquals('admin', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
    }

    public function testUpdatingScannerUsernameWhenScannerChangedByGod() {
        $this->handleCommandBypassingFirewall(
            ResourceGodUpdateCommand::builder()
                ->setResource($this->phpBook)
                ->setNewContents($this->phpBook->getContents()->withReplacedValues($this->findMetadataByName('skanista'), 1))
                ->build()
        );
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertEquals('admin', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
    }

    public function testCalculatingForNewResource() {
        $resource = $this->handleCommandBypassingFirewall(
            new ResourceCreateCommand(
                $this->phpBook->getKind(),
                ResourceContents::fromArray([$this->findMetadataByName('skanista')->getId() => 1])
            )
        );
        $this->assertEquals('admin', $resource->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testNotUpdatingNotDependentMetadata() {
        $displayStrategyMetadata = $this->findMetadataByName('nazwaNadzorujacego');
        $this->updateDisplayStrategyOfPhpBookKind($displayStrategyMetadata, '{{ random(1000) }}');
        $number = $this->phpBook->getValues($displayStrategyMetadata)[0]->getValue();
        $this->assertGreaterThanOrEqual(0, $number);
        $this->changeScannerUsernameToNewScanner();
        $this->assertEquals($number, $this->phpBook->getValues($displayStrategyMetadata)[0]->getValue());
    }

    public function testUpdatingMetadataDependentOnAnotherDisplayStrategyMetadata() {
        $displayStrategyMetadata = $this->findMetadataByName('nazwaNadzorujacego');
        $this->updateDisplayStrategyOfPhpBookKind($displayStrategyMetadata, '{{ r|mNazwaSkanisty|upper }}');
        $upperScanner = $this->phpBook->getValues($displayStrategyMetadata)[0]->getValue();
        $this->assertEquals('SKANER', $upperScanner);
        $this->changeScannerUsernameToNewScanner();
        $this->assertEquals('NOWYSKANER', $this->phpBook->getValues($displayStrategyMetadata)[0]->getValue());
    }

    public function testMetadataDependentOnEachOther() {
        $displayStrategyMetadata = $this->findMetadataByName('nazwaNadzorujacego');
        $this->updateDisplayStrategyOfPhpBookKind($displayStrategyMetadata, '{{ r|mNazwaSkanisty|upper }}');
        $this->updateDisplayStrategyOfPhpBookKind($this->scannerUsernameMetadata, '{{ r|mNazwaNadzorujacego|capitalize }}');
        $upperScanner = $this->phpBook->getValues($displayStrategyMetadata)[0]->getValue();
        $this->assertEquals('SKANER', $upperScanner);
        $capitalizedScanner = $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue();
        $this->assertEquals('Skaner', $capitalizedScanner);
    }

    public function testMetadataDependentOnEachOtherGivesUpInfiniteRecursion() {
        $displayStrategyMetadata = $this->findMetadataByName('nazwaNadzorujacego');
        $this->updateDisplayStrategyOfPhpBookKind($displayStrategyMetadata, '{{ r|mNazwaSkanisty|upper }} {{ random(1000) }}');
        $this->updateDisplayStrategyOfPhpBookKind(
            $this->scannerUsernameMetadata,
            '{{ r|mNazwaNadzorujacego|capitalize }} {{ random(1000) }}'
        );
        $upperScanner = $this->phpBook->getValues($displayStrategyMetadata)[0]->getValue();
        $this->assertContains('SKANER', $upperScanner);
        $capitalizedScanner = $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue();
        $this->assertContains('Skaner', $capitalizedScanner);
    }

    public function testMarksDirtyIfTooManyResources() {
        $skanistaId = $this->findMetadataByName('skanista')->getId();
        /** @var ResourceEntity $resource */
        $resource = $this->handleCommandBypassingFirewall(
            new ResourceCreateCommand(
                $this->phpBook->getKind(),
                ResourceContents::fromArray([$skanistaId => $this->phpBook->getValues($skanistaId)[0]->getValue()])
            )
        );
        for ($i = 0; $i < 30; $i++) {
            $cloned = clone $resource;
            $this->getEntityManager()->persist($cloned);
        }
        $this->getEntityManager()->flush();
        $this->changeScannerUsernameToNewScanner();
        $this->getEntityManager()->refresh($resource);
        $this->assertTrue($resource->isDisplayStrategiesDirty());
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertTrue($this->phpBook->isDisplayStrategiesDirty());
        $this->assertEquals('skaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testEvaluatingDirtyResourcesWithCrontab() {
        $this->testMarksDirtyIfTooManyResources();
        $this->executeCommand('repeka:cyclic-tasks:dispatch');
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
        $this->assertEquals('nowyskaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testMarksDirtyOnResourceKindDisplayStrategyChange() {
        $this->updateDisplayStrategyOfPhpBookKind($this->scannerUsernameMetadata, '{{ random(10) }}', false);
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertTrue($this->phpBook->isDisplayStrategiesDirty());
        $this->assertEquals('skaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testDoesNotMarksDirtyIfNoDisplayStrategyHasChanged() {
        $this->updateDisplayStrategyOfPhpBookKind(SystemMetadata::USERNAME()->toMetadata(), '{{ random(10) }}', false);
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
    }

    public function testMarksDirtyOnMetadataDisplayStrategyChange() {
        $this->handleCommandBypassingFirewall(
            new MetadataUpdateCommand(
                $this->scannerUsernameMetadata,
                $this->scannerUsernameMetadata->getLabel(),
                $this->scannerUsernameMetadata->getDescription(),
                $this->scannerUsernameMetadata->getPlaceholder(),
                [],
                'basic',
                '{{ random(10) }}',
                false,
                false
            )
        );
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertTrue($this->phpBook->isDisplayStrategiesDirty());
        $this->assertEquals('skaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testDoesNotMarkDirtyIfMetadataDisplayStrategyDoesNotChange() {
        $this->handleCommandBypassingFirewall(
            new MetadataUpdateCommand(
                $this->scannerUsernameMetadata,
                $this->scannerUsernameMetadata->getLabel(),
                $this->scannerUsernameMetadata->getDescription(),
                $this->scannerUsernameMetadata->getPlaceholder(),
                $this->scannerUsernameMetadata->getConstraints(),
                'basic',
                $this->scannerUsernameMetadata->getDisplayStrategy(),
                false,
                false
            )
        );
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
        $this->assertEquals('skaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testDoesNotMarkDirtyOnMetadataDisplayStrategyChangeIfOverriddenInResourceKind() {
        $this->updateDisplayStrategyOfPhpBookKind($this->scannerUsernameMetadata, 'UNICORN');
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
        $this->handleCommandBypassingFirewall(
            new MetadataUpdateCommand(
                $this->scannerUsernameMetadata,
                $this->scannerUsernameMetadata->getLabel(),
                $this->scannerUsernameMetadata->getDescription(),
                $this->scannerUsernameMetadata->getPlaceholder(),
                [],
                'basic',
                '{{ random(10) }}',
                false,
                false
            )
        );
        $this->getEntityManager()->refresh($this->phpBook);
        $this->assertFalse($this->phpBook->isDisplayStrategiesDirty());
        $this->assertEquals('UNICORN', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
    }

    public function testGeneratingElasticDate() {
        $m = $this->findMetadataByName('dataWydania');
        $displayStrategy = '{"from": "{{\'\'now\'\'|date(\'\'Y\'\')}}-10-30"}';
        $this->getEntityManager()->getConnection()->exec("UPDATE metadata SET display_strategy='$displayStrategy' WHERE id={$m->getId()}");
        $this->resetEntityManager(MetadataRepository::class, ResourceRepository::class);
        $this->phpBook = $this->getEntityManager()->find(ResourceEntity::class, $this->phpBook->getId());
        $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->phpBook, [$m->getId()]));
        $this->getEntityManager()->refresh($this->phpBook);
        $date = $this->phpBook->getValues($m);
        $this->assertCount(1, $date);
        $this->assertContains('30.10.' . date('Y'), (string)$date[0]);
    }

    /** @small */
    public function testParentPathDynamicMetadataContainsIntegersNotStrings() {
        $webpackBook = $this->findResourceByContents(['tytul' => 'webpacka']);
        $topParent = $webpackBook->getValues($this->findMetadataByName('parentPath'));
        $this->assertCount(1, $topParent);
        $this->assertTrue(is_int($topParent[0]->getValue()));
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    private function updateDisplayStrategyOfPhpBookKind(Metadata $metadataToChange, string $template, bool $evaluate = true) {
        $newMetadataOverrides = array_map(
            function (Metadata $metadata) use ($template, $metadataToChange) {
                $override = array_merge(['id' => $metadata->getId()], $metadata->getOverrides());
                if ($metadata->getId() == $metadataToChange->getId()) {
                    $override['displayStrategy'] = $template;
                }
                return $override;
            },
            $this->phpBook->getKind()->getMetadataList()
        );
        $this->handleCommandBypassingFirewall(
            new ResourceKindUpdateCommand(
                $this->phpBook->getKind(),
                $this->phpBook->getKind()->getLabel(),
                $newMetadataOverrides,
                $this->phpBook->getWorkflow()
            )
        );
        if ($evaluate) {
            $this->getEntityManager()->refresh($this->phpBook);
            $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->phpBook));
        }
    }
}
