<?php
namespace Repeka\Tests\Integration\UseCase\MetadataImport;

use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\MetadataImport\XmlExtractQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

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
        $yamlParser = new Parser();
        $config = $yamlParser->parseFile(__DIR__ . '/dumps/100000231332.yml', Yaml::PARSE_CONSTANT | Yaml::PARSE_CUSTOM_TAGS);
        $importConfig = $this->container->get(ImportConfigFactory::class)->fromArray($config, $rk);
        $resourceXml = file_get_contents(__DIR__ . '/dumps/100000231332.marcxml');
        $extractedValues = $this->handleCommandBypassingFirewall(new XmlExtractQuery($resourceXml, $config['xmlMappings']));
        $importedValues = $this->handleCommandBypassingFirewall(new MetadataImportQuery($extractedValues, $importConfig))
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
