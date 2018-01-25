<?php
namespace Repeka\Tests\Integration\UseCase\MetadataImport;

use Repeka\Domain\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\MetadataImport\XmlExtractQuery;
use Repeka\Tests\IntegrationTestCase;

class XmlMetadataImportIntegrationTest extends IntegrationTestCase {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    /** @before */
    public function loadFixtures() {
        $this->loadAllFixtures();
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
    }

    public function testFlagExampleImport() {
        $rk = $this->resourceKindRepository->findOne(1);
        $config = json_decode(file_get_contents(__DIR__ . '/100000231332.json'), true);
        $importConfig = $this->container->get(ImportConfigFactory::class)->fromArray($config, $rk);
        $resourceXml = file_get_contents(__DIR__ . '/100000231332.marcxml');
        $extractedValues = $this->handleCommand(new XmlExtractQuery($resourceXml, $config['xmlMappings']));
        $importedValues = $this->handleCommand(new MetadataImportQuery($extractedValues, $importConfig))->getAcceptedValues();
        $this->assertContains([['value' => 'Artyści obcy w służbie polskiej']], $importedValues);
        $this->assertContains([['value' => 'Artyści obcy w służbie polskiej - epizody z dziejów sztuki']], $importedValues);
        $this->assertContains([['value' => 'pol']], $importedValues);
    }
}
