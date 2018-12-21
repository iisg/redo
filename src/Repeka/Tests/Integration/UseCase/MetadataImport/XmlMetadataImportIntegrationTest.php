<?php
namespace Repeka\Tests\Integration\UseCase\MetadataImport;

use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\MetadataImport\MarcxmlExtractQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class XmlMetadataImportIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    /** @before */
    public function loadFixtures() {
        $this->loadAllFixtures();
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
    }

    public function testFlagExampleImport() {
        $rk = $this->resourceKindRepository->findOne(1);
        $importedId = 100000231332;
        $config = $this->container->get(ImportConfigFactory::class)->fromFile(__DIR__ . '/dumps/100000231332.yml', $rk);
        $resourceXml = file_get_contents(__DIR__ . '/dumps/100000231332.marcxml');
        $extractedValues = $this->handleCommandBypassingFirewall(new MarcxmlExtractQuery($resourceXml, $importedId));
        $importedValues = $this->handleCommandBypassingFirewall(new MetadataImportQuery($extractedValues, $config))
            ->getAcceptedValues();
        $this->assertContains([['value' => 'Artyści obcy w służbie polskiej']], $importedValues);
        $this->assertContains(
            [['value' => 'Artyści obcy w służbie polskiej - epizody z dziejów sztuki']],
            $importedValues
        );
        $this->assertContains([['value' => 'pol']], $importedValues);
        $this->assertContains([['value' => 4962]], $importedValues);
        $publishingHouse = $this->findResourceByContents(['Nazwa' => 'Narodowego']);
        $this->assertContains([['value' => $publishingHouse->getId()]], $importedValues);
    }
}
