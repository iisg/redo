<?php
namespace Repeka\Tests\Integration\UseCase\MetadataImport;

use DateTime;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\MetadataImport\MarcxmlExtractQuery;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Tests\Integration\Migrations\DatabaseMigrationTestCase;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class ConfigurationForOldSystemImportIntegrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    /** @var MetadataRepository */
    private $metadataRepository;

    /** @var ResourceKind */
    private $testResourceKind;
    private $configPath;

    /** @before */
    public function before() {

        $this->getEntityManager()->getConnection()->exec(file_get_contents(__DIR__ . '/dumps/only_language_resources_pgdump.sql'));
        $this->container = self::createClient()->getContainer();
        $this->resourceKindRepository = $this->container->get(ResourceKindRepository::class);
        $this->metadataRepository = $this->container->get(MetadataRepository::class);
        $metadata = [];
        $ids = [53, 156, 125, 4, 63, 62, 99, 3, 61, 64, 65, 5, 172, 66, 136, 70, 86, 181, 135, 72, 79, 76, 117, 173];
        foreach ($ids as $id) {
            $metadata[] = $this->metadataRepository->findOne($id);
        }
        $this->testResourceKind = $this->createResourceKind(
            ['PL' => 'testResourceKind', 'GB' => 'testResourceKind'],
            $metadata
        );
        $this->configPath = __DIR__ . '/dumps/marc-import-config.yml';
    }

    private function toFlexibleDateArray($from, $to): array {
        $flexibleDate = [
            'from' => DateTime::createFromFormat('Y', $from)->format(DateTime::ATOM),
            'to' => DateTime::createFromFormat('Y', $to)->format(DateTime::ATOM),
            'mode' => MetadataDateControlMode::RANGE,
            'rangeMode' => MetadataDateControlMode::YEAR,
        ];
        return MetadataDateControlConverterUtil::convertDateToFlexibleDate(
            $flexibleDate['from'],
            $flexibleDate['to'],
            $flexibleDate['mode'],
            $flexibleDate['rangeMode']
        )->toArray();
    }

    public function testImportFromMarcXml1() {
        $filePath = __DIR__ . '/dumps/bib-103684.marcxml';
        $importedId = 100000228666;
        $expectedImportedValues = [
            53 => [['value' => 502]],
            156 => [['value' => 'zz2004984987']],
            4 => [
                [
                    'value' => 'Skimborowicz, Hipolit',
                    'submetadata' => [
                        147 => [
                            [
                                'value' => $this->toFlexibleDateArray('1815', '1880'),
                                'submetadata' => [301 => [['value' => '1815-1880']]],
                            ],
                        ],
                        179 => [['value' => '113281']],
                        180 => [['value' => 'n  99707033']],
                    ],
                ],
                [
                    'value' => 'Gerson, Wojciech',
                    'submetadata' => [
                        147 => [
                            [
                                'value' => $this->toFlexibleDateArray('1831', '1901'),
                                'submetadata' => [301 => [['value' => '1831-1901']]],
                            ],
                        ],
                        179 => [['value' => '100004']],
                        180 => [['value' => 'n  93090075']],
                    ],
                ],
            ],
            3 => [
                [
                    'value' =>
                        'Willanów : album : zbiór widoków i pamiątek oraz kopje z obrazów Galeryi Willanowskiej wykonane na drzewie' .
                        ' w Drzeworytni Warszawskiej',
                ],
            ],
            61 => [
                ['value' => 'Tytuł okładkowy: Album Willanowa'],
                ['value' => 'Tytuł z dodatkowej strony tytułowej: Willanów dawny i teraźniejszy'],
                ['value' => 'Tytuł w żywej paginie: Album Willanowski'],
                ['value' => 'Wilanów'],
                [
                    'value' =>
                        'Willanów : album widoków i pamiątek oraz kopje z obrazów Galerii Willanowskiej wykonane na drzewie' .
                        ' w Drzeworytni Warszawskiej',
                ],
                [
                    'value' =>
                        'Willanów : album widoków i pamiątek oraz kopie z obrazów Galerii Willanowskiej wykonane na drzewie' .
                        ' w Drzeworytni Warszawskiej',
                ],
                ['value' => 'Tytuł okładkowy: Wilanów : album widoków obrazów i pamiątek z opisem'],
            ],
            64 => [['value' => 'Warszawa']],
            65 => [['value' => 'nakł. S. Orgelbranda Synów :'], ['value' => 'Drzeworytnia Warszawska']],
            5 => [
                [
                    'value' => MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                        '1877',
                        null,
                        MetadataDateControlMode::YEAR,
                        null
                    )->toArray(),
                    'submetadata' => [303 => [['value' => '1877']]],
                ],
            ],
            172 => [['value' => 'Warszawa']],
            66 => [['value' => 'dr. S. Orgelbranda Synów']],
            136 => [['value' => '[4], 186, II s., [48] k. tabl.']],
            86 => [['value' => false]],
            181 => [['value' => 'EDT_4 Do udostępnienia na zewnątrz']],
            79 => [['value' => 'IV-37861']],
            76 => [['value' => '100000228666']],
            117 => [['value' => '100000228666']],
            173 => [['value' => '103684']],
        ];
        $this->configImportTest($filePath, $this->configPath, $expectedImportedValues, $importedId);
    }

    /** @SuppressWarnings("PHPMD.ExcessiveMethodLength") */
    public function testImportFromMarcXml2() {
        $filePath = __DIR__ . '/dumps/bib-136095.marcxml';
        $importedId = 100000305812;
        $expectedImportedValues = [
            53 => [['value' => 511]],
            156 => [['value' => 'xx004361056']],
            63 => [
                [
                    'value' => 'Franzius, Georg',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114802']],
                        188 => [['value' => 'n 2006115366']],
                    ],
                ],
                [
                    'value' => 'Frühling, August',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114801']],
                        188 => [['value' => 'n  00052595']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1847', '1910'),
                                'submetadata' => [302 => [['value' => '1847-1910']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Garbe, Heinrich',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114768']],
                        188 => [['value' => 'n 2009148736']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1840', '1901'),
                                'submetadata' => [302 => [['value' => '1840-1901']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Hess, August',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '116096']],
                        188 => [['value' => 'n 2017034023']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1827', '1894'),
                                'submetadata' => [302 => [['value' => '1827-1894']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Lincke, Felix',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114490']],
                        188 => [['value' => 'n 2006095632']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1840', '1917'),
                                'submetadata' => [302 => [['value' => '1840-1917']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Pestalozzi, Karl',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '120897']],
                        188 => [['value' => 'n 2018128701']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1825', '1891'),
                                'submetadata' => [302 => [['value' => '1825-1891']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Schlichting, Julius',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114861']],
                        188 => [['value' => 'n 2012060437']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1835', '1894'),
                                'submetadata' => [302 => [['value' => '1835-1894']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Schmitt, Eduard',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '110144']],
                        188 => [['value' => 'n 2005134349']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1842', '1913'),
                                'submetadata' => [302 => [['value' => '1842-1913']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Franzius, Ludwig',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114488']],
                        188 => [['value' => 'n 2006115367']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1832', '1903'),
                                'submetadata' => [302 => [['value' => '1832-1903']]],
                            ],
                        ],
                    ],
                ],
                [
                    'value' => 'Sonne, Eduard',
                    'submetadata' => [
                        176 => [['value' => 'Redaktor']],
                        187 => [['value' => '114767']],
                        188 => [['value' => 'n 2006122145']],
                        182 => [
                            [
                                'value' => $this->toFlexibleDateArray('1828', '1917'),
                                'submetadata' => [302 => [['value' => '1828-1917']]],
                            ],
                        ],
                    ],
                ],
            ],
            3 => [['value' => 'Der Wasserbau']],
            64 => [['value' => 'Leipzig']],
            65 => [['value' => 'Verlag von Wilhelm Engelmann']],
            5 => [
                [
                    'value' => MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                        '1879',
                        null,
                        MetadataDateControlMode::YEAR,
                        null
                    )->toArray(),
                    'submetadata' => [303 => [['value' => '1879']]],
                ],
            ],
            136 => [['value' => 'XVI, 1158 stron, [1] karta tablic złożona']],
            99 => [['value' => 'Dodatek do tytułu: Atlas : XVI stron, LXVII kart tablic złożonych.']],
            86 => [['value' => false]],
            181 => [['value' => 'EDT_2 Nieznana data śmierci współautora']],
            135 => [['value' => 'Handbuch der Ingenieurwissenschaften in vier Bänden Bd. 3']],
            79 => [['value' => 'III-307350']],
            76 => [['value' => '100000305812']],
            117 => [['value' => '100000305812']],
            173 => [['value' => '136095']],
        ];
        $this->configImportTest($filePath, $this->configPath, $expectedImportedValues, $importedId);
    }

    private function configImportTest($resourcePath, $configPath, array $expectedImportValues, $importedId) {
        $config = $this->container->get(ImportConfigFactory::class)->fromFile($configPath, $this->testResourceKind);
        $resourceXml = file_get_contents($resourcePath);
        $extractedValues = $this->handleCommandBypassingFirewall(new MarcxmlExtractQuery($resourceXml, $importedId));
        $importedValues = $this->handleCommandBypassingFirewall(new MetadataImportQuery($extractedValues, $config))
            ->getAcceptedValues();
        $this->assertEquals($expectedImportValues, $importedValues->toArray());
    }
}
