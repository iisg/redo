<?php
namespace Repeka\Tests\Integration\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class UpdatingDependentDisplayStrategiesMetadataIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $phpBook;
    /** @var ResourceEntity */
    private $scannerData;
    /** @var Metadata */
    private $scannerUsernameMetadata;

    /** @before */
    public function init() {
        $this->loadAllFixtures();
        $this->phpBook = $this->getPhpBookResource();
        $this->scannerData = $this->findResourceByContents([SystemMetadata::USERNAME => 'skaner']);
        $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->phpBook));
        $this->scannerUsernameMetadata = $this->findMetadataByName('nazwaSkanisty');
    }

    public function testUpdatingScannerUsername() {
        $this->assertEquals('skaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
        $this->handleCommandBypassingFirewall(
            new ResourceUpdateContentsCommand(
                $this->scannerData,
                $this->scannerData->getContents()->withReplacedValues(SystemMetadata::USERNAME, 'nowyskaner')
            )
        );
        $this->phpBook = $this->getPhpBookResource();
        $this->assertEquals('nowyskaner', $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue());
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
        $detailsPage = $this->findMetadataByName('detailsPage');
        $this->updateDisplayStrategyOfPhpBookKind($detailsPage, '{{ random(1000) }}');
        $number = $this->phpBook->getValues($detailsPage)[0]->getValue();
        $this->assertGreaterThanOrEqual(0, $number);
        $this->testUpdatingScannerUsername();
        $this->assertEquals($number, $this->phpBook->getValues($detailsPage)[0]->getValue());
    }

    public function testUpdatingMetadataDependentOnAnotherDisplayStrategyMetadata() {
        $detailsPage = $this->findMetadataByName('detailsPage');
        $this->updateDisplayStrategyOfPhpBookKind($detailsPage, '{{ r|mNazwaSkanisty|upper }}');
        $upperScanner = $this->phpBook->getValues($detailsPage)[0]->getValue();
        $this->assertEquals('SKANER', $upperScanner);
        $this->testUpdatingScannerUsername();
        $this->assertEquals('NOWYSKANER', $this->phpBook->getValues($detailsPage)[0]->getValue());
    }

    public function testMetadataDependentOnEachOther() {
        $detailsPage = $this->findMetadataByName('detailsPage');
        $this->updateDisplayStrategyOfPhpBookKind($detailsPage, '{{ r|mNazwaSkanisty|upper }}');
        $this->updateDisplayStrategyOfPhpBookKind($this->scannerUsernameMetadata, '{{ r|mDetailsPage|capitalize }}');
        $upperScanner = $this->phpBook->getValues($detailsPage)[0]->getValue();
        $this->assertEquals('SKANER', $upperScanner);
        $capitalizedScanner = $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue();
        $this->assertEquals('Skaner', $capitalizedScanner);
    }

    public function testMetadataDependentOnEachOtherGivesUpInfiniteRecursion() {
        $detailsPage = $this->findMetadataByName('detailsPage');
        $this->updateDisplayStrategyOfPhpBookKind($detailsPage, '{{ r|mNazwaSkanisty|upper }} {{ random(1000) }}');
        $this->updateDisplayStrategyOfPhpBookKind($this->scannerUsernameMetadata, '{{ r|mDetailsPage|capitalize }} {{ random(1000) }}');
        $upperScanner = $this->phpBook->getValues($detailsPage)[0]->getValue();
        $this->assertContains('SKANER', $upperScanner);
        $capitalizedScanner = $this->phpBook->getValues($this->scannerUsernameMetadata)[0]->getValue();
        $this->assertContains('Skaner', $capitalizedScanner);
    }

    private function updateDisplayStrategyOfPhpBookKind(Metadata $metadataToChange, string $template) {
        $newMetadataOverrides = array_map(
            function (Metadata $metadata) use ($template, $metadataToChange) {
                $override = array_merge(['id' => $metadata->getId()], $metadata->getOverrides());
                if ($metadata->getId() == $metadataToChange->getId()) {
                    $override['constraints'] = ['displayStrategy' => $template];
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
        $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->phpBook));
    }
}
