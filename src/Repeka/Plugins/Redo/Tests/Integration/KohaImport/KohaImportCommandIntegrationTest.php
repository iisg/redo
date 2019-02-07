<?php
namespace Repeka\Plugins\Redo\Tests\Integration\KohaImport;

use DateTime;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;
use Repeka\Tests\Integration\Migrations\DatabaseMigrationTestCase;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
class KohaImportCommandIntegrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    private $barcode1;
    private $barcode2;
    private $barcodeMetadataId;
    private $configPath;
    private $resourceKindId;

    private $expectedResourceContents1;
    private $expectedResourceContents2;

    /** @before */
    public function init(): void {
        $this->getEntityManager()->getConnection()->exec(
            file_get_contents(__DIR__ . '/../MetadataImport/dumps/only_language_resources_pgdump.sql')
        );
        $this->getEntityManager()->getConnection()->exec(
            file_get_contents(__DIR__.'/../MetadataImport/dumps/only_language_resources_fix_metadata.sql')
        );
        $this->barcode1 = 100000231505;
        $this->barcode2 = 100000231454;
        $this->barcodeMetadataId = 76;
        $this->resourceKindId = 1;
        $this->configPath = addcslashes(__DIR__ . '/../MetadataImport/dumps/marc-import-config.yml', '\\');
        $this->expectedResourceContents1 = [
            53 => [['value' => 502]],
            156 => [['value' => 'zz2006963444']],
            125 => [['value' => 5466]],
            4 => [
                [
                    'value' => 'Osiński, Marian',
                    'submetadata' => [
                        147 => [
                            [
                                'value' => MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                                    DateTime::createFromFormat('Y', '1883')->format(DateTime::ATOM),
                                    DateTime::createFromFormat('Y', '1974')->format(DateTime::ATOM),
                                    MetadataDateControlMode::RANGE,
                                    MetadataDateControlMode::YEAR
                                )->toArray(),
                                'submetadata' => [199 => [['value' => '1883-1974']]],
                            ],
                        ],
                        179 => [['value' => '108162']],
                        180 => [['value' => 'n 2006133877']],
                    ],
                ],
            ],
            3 => [
                [
                    'value' => 'Zamek w Żółkwi',
                    "submetadata" => [
                        162 => [
                            [
                                "value" => 502,
                            ],
                        ],
                    ],
                ],
            ],
            64 => [['value' => 'Lwów']],
            65 => [['value' => 'nakł. Towarzystwa Opieki nad Zabytkami Sztuki i Kultury']],
            5 => [
                [
                    'value' =>
                        MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                            DateTime::createFromFormat('Y', '1933')->format(DateTime::ATOM),
                            null,
                            MetadataDateControlMode::YEAR,
                            null
                        )->toArray(),
                    'submetadata' => [201 => [['value' => '1933']]],
                ],
            ],
            136 => [['value' => '140, [4] s., VI k. tabl. (niektóre złoż.)']],
            172 => [['value' => 'Lwów']],
            66 => [['value' => 'Zakł. Graf. Ap. Akc. Książnica-Atlas']],
            181 => [['value' => 'EDT_3 Nie do udostępnienia na zewnątrz']],
            79 => [['value' => 'II-22122']],
            76 => [['value' => '100000231505']],
            117 => [['value' => '100000231505']],
            173 => [['value' => '110507']],
            -1 => [["value" => 1963,],],
            89 => [["value" => 162,],],
            90 => [["value" => "4(4) zał.  - (32x24)",]],
            92 => [
                [
                    "value" => "Skaner A2 dodatkowy\r\nW suw liczba stron 150\r\nRozkładówki i robione trybem glass plate mode i high",
                ],
            ],
            94 => [["value" => 1924,],],
            95 => [["value" => 1937,],],
            96 => [["value" => 6,],],
            107 => [["value" => 1927,],],
            157 => [["value" => "A-4",],],
        ];
        $this->expectedResourceContents2 = [
            53 => [['value' => 502]],
            156 => [['value' => 'zz2003874225']],
            4 => [
                [
                    'value' => 'Dyboski, Roman',
                    'submetadata' => [
                        147 => [
                            [
                                'value' => MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                                    DateTime::createFromFormat('Y', '1883')->format(DateTime::ATOM),
                                    DateTime::createFromFormat('Y', '1945')->format(DateTime::ATOM),
                                    MetadataDateControlMode::RANGE,
                                    MetadataDateControlMode::YEAR
                                )->toArray(),
                                'submetadata' => [199 => [['value' => '1883-1945']]],
                            ],
                        ],
                        179 => [['value' => '108005']],
                        180 => [['value' => 'n  97019158']],
                    ],
                ],
            ],
            3 => [
                [
                    'value' => 'Stany Zjednoczone Ameryki Północnej : wrażenia i refleksje',
                    "submetadata" => [
                        162 => [
                            [
                                "value" => 502,
                            ],
                        ],
                    ],
                ],
            ],
            64 => [['value' => 'Lwów'], ['value' => 'Warszawa']],
            65 => [['value' => 'Książnica-Atlas']],
            5 => [
                [
                    'value' =>
                        MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                            DateTime::createFromFormat('Y', '1930')->format(DateTime::ATOM),
                            null,
                            MetadataDateControlMode::YEAR,
                            null
                        )->toArray(),
                    'submetadata' => [201 => [['value' => '1930']]],
                ],
            ],
            136 => [['value' => '330, [2] s., [1] k. tabl. złoż.']],
            70 => [['value' => '1. [na s. przytyt. odręczna notatka:] Boże Narodzenie 1930 roku- Jurkowi-Halka']],
            172 => [['value' => 'Lwów']],
            66 => [['value' => 'Zakłady Graficzne Ski Akc. Książnica-Atlas we Lwowie']],
            181 => [['value' => 'EDT_4 Do udostępnienia na zewnątrz']],
            79 => [['value' => 'II-26661']],
            76 => [['value' => '100000231454']],
            117 => [['value' => '100000231454']],
            173 => [['value' => '90075']],
            -1 => [["value" => 1963]],
            89 => [["value" => 342]],
            92 => [["value" => "Skaner A2 dodatkowy"]],
            94 => [["value" => 1924]],
            95 => [["value" => 1937]],
            96 => [["value" => 9]],
            107 => [["value" => 1927]],
            157 => [["value" => "A-4"]],
            158 => [["value" => "2- 19x42"]],
            159 => [["value" => "odręczne wpisy, notatki"]],
        ];
    }

    public function testImportingOneResource() {
        $this->import($this->resourceKindId, $this->barcode1);
        $updatedResourceContents = $this->getUpdatedResourceContents($this->barcode1);
        $this->containsMetadataValueValues($updatedResourceContents, $this->expectedResourceContents1);
    }

    public function testImportingMultipleTimesDoesNotReplicateData() {
        $this->import($this->resourceKindId, $this->barcode1);
        $this->import($this->resourceKindId, $this->barcode1);
        $resourceContents = $this->getUpdatedResourcecontents($this->barcode1);
        foreach ($resourceContents->toArray() as $metadataValues) {
            $this->assertEquals($metadataValues, array_unique($metadataValues, SORT_REGULAR));
        }
    }

    public function testImportingAllResources() {
        // in the dump there are 2 files from Lwowiana
        $this->import($this->resourceKindId);
        $updatedResourceContents1 = $this->getUpdatedResourcecontents($this->barcode1);
        $this->containsMetadataValueValues($updatedResourceContents1, $this->expectedResourceContents1);
        $updatedResourceContents2 = $this->getUpdatedResourcecontents($this->barcode2);
        $this->containsMetadataValueValues($updatedResourceContents2, $this->expectedResourceContents2);
    }

    private function getUpdatedResourceContents($barcode): ?ResourceContents {
        $this->container = self::createAdminClient()->getContainer();
        $this->resourceRepository = $this->getResourceRepository();
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
        $resourceKind = $this->resourceKindRepository->findOne($this->resourceKindId);
        $builder = new ResourceListQueryBuilder();
        $query = $builder->filterByContents([$this->barcodeMetadataId => $barcode])->filterByResourceKind($resourceKind)->build();
        $resources = $this->resourceRepository->findByQuery($query);
        return $resources->getResults()[0]->getContents();
    }

    private function containsMetadataValueValues(ResourceContents $resourceContents, $expectedValuesArray) {
        $resourceContentsArray = $resourceContents->toArray();
        unset($resourceContentsArray[-5]); //etykieta
        $this->assertEquals($expectedValuesArray, $resourceContentsArray);
    }

    protected function import($resourceKindId, $barcode = null) {
        $command = "redo:koha:import --resourceKindId $resourceKindId";
        if ($barcode) {
            $command = $command . " --barcode $barcode";
        }
        $this->executeCommand($command);
    }
}
