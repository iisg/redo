<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Elasticsearch\Model\ESContentsAdjuster;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ESContentsAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ESContentsAdjuster */
    private $esContentsAdjuster;
    /** @var ResourceEntity */
    private $resource;

    protected function setUp() {
        $metadataRepository = $this->createMetadataRepositoryStub(
            [
                $this->createMetadataMock(1, null, MetadataControl::TEXT(), [], 'books', [], 'TEXT1'),
                $this->createMetadataMock(2, null, MetadataControl::INTEGER(), [], 'books', [], 'INTEGER'),
                $this->createMetadataMock(3, null, MetadataControl::TIMESTAMP(), [], 'books', [], 'TIMESTAMP'),
                $this->createMetadataMock(4, null, MetadataControl::FILE(), [], 'books', [], 'FILE'),
            ]
        );
        $this->resource = $this->createResourceMock(1);
        $container = $this->createMock(ContainerInterface::class);
        $resourceFileStorage = $this->createMock(ResourceFileStorage::class);
        $resourceFileStorage->method('getFileContents')->willReturn('mocked content');
        $container->method('get')->willReturn($resourceFileStorage);
        $this->esContentsAdjuster = new ESContentsAdjuster($metadataRepository, $container);
    }

    public function testAdjustEmptyContents() {
        $this->assertEquals([], $this->esContentsAdjuster->adjustContents($this->resource, []));
    }

    public function testAdjustContentsWithCorrectOneLevelMetadata() {
        $contentsToAdjust = [
            1 => [['value' => 'a']],
            2 => [['value' => 5]],
        ];
        $contentsAfterAdjust = [
            1 => [['value_text' => 'a']],
            2 => [['value_integer' => 5]],
        ];
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($this->resource, $contentsToAdjust));
    }

    public function testAdjustContentsWithSubmetadata() {
        $contentsToAdjust = [
            1 => [
                [
                    'value' => 'a',
                    'submetadata' => [
                        3 => [['value' => '03-08-2018']],
                    ],
                ],
                [
                    'value' => 'b',
                    'submetadata' => [
                        3 => [['value' => '09-09-2018']],
                    ],
                ],
            ],
        ];
        $contentsAfterAdjust = [
            1 => [
                [
                    'value_text' => 'a',
                    'submetadata' => [
                        3 => [['value_timestamp' => '03-08-2018']],
                    ],
                ],
                [
                    'value_text' => 'b',
                    'submetadata' => [
                        3 => [['value_timestamp' => '09-09-2018']],
                    ],
                ],
            ],
        ];
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($this->resource, $contentsToAdjust));
    }

    public function testAdjustFileContents() {
        $contentsToAdjust = [
            4 => [
                ['value' => 'file.unsupported'],
                ['value' => 'a'],
                ['value' => 'file.txt'],
                ['value' => '/home/user/file.txt'],
            ],
        ];
        $contentsAfterAdjust = [
            4 => [
                ['value_file' => [FtsConstants::NAME => 'file.unsupported']],
                ['value_file' => [FtsConstants::NAME => 'a']],
                ['value_file' => [FtsConstants::NAME => 'file.txt', FtsConstants::CONTENT => 'mocked content']],
                ['value_file' => [FtsConstants::NAME => 'file.txt', FtsConstants::CONTENT => 'mocked content']],
            ],
        ];
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($this->resource, $contentsToAdjust));
    }
}
