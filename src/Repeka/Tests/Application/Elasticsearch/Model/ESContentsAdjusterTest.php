<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Model\ESContentsAdjuster;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Tests\Traits\StubsTrait;

class ESContentsAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ESContentsAdjuster */
    private $esContentsAdjuster;

    protected function setUp() {
        $metadataRepository = $this->createMetadataRepositoryStub(
            [
                $this->createMetadataMock(1, null, MetadataControl::TEXT(), [], 'books', [], 'TEXT1'),
                $this->createMetadataMock(2, null, MetadataControl::INTEGER(), [], 'books', [], 'INTEGER'),
                $this->createMetadataMock(3, null, MetadataControl::DATE(), [], 'books', [], 'DATE'),
                $this->createMetadataMock(4, null, MetadataControl::FILE(), [], 'books', [], 'FILE'),
            ]
        );
        $this->esContentsAdjuster = new ESContentsAdjuster($metadataRepository);
    }

    public function testAdjustEmptyContents() {
        $this->assertEquals([], $this->esContentsAdjuster->adjustContents([]));
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
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($contentsToAdjust));
    }

    public function testAdjustContentsWithNotIndexedMetadata() {
        $contentsToAdjust = [
            1 => [['value' => 'a']],
            4 => [['value' => 'slfhddlfksd;sdksngs']],
        ];
        $contentsAfterAdjust = [
            1 => [['value_text' => 'a']],
        ];
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($contentsToAdjust));
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
                        3 => [['value_date' => '03-08-2018']],
                    ],
                ],
                [
                    'value_text' => 'b',
                    'submetadata' => [
                        3 => [['value_date' => '09-09-2018']],
                    ],
                ],
            ],
        ];
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($contentsToAdjust));
    }

    public function testAdjustContentsWithSubmetadataAndNotIndexedParentMetadata() {
        $contentsToAdjust = [
            4 => [
                [
                    'value' => 'alsdkfj',
                    'submetadata' => [
                        3 => [['value' => '03-08-2018']],
                    ],
                ],
                [
                    'value' => 'blsdjf',
                    'submetadata' => [
                        3 => [['value' => '09-09-2018']],
                    ],
                ],
            ],
        ];
        $contentsAfterAdjust = [
            4 => [
                [
                    'submetadata' => [
                        3 => [['value_date' => '03-08-2018']],
                    ],
                ],
                [
                    'submetadata' => [
                        3 => [['value_date' => '09-09-2018']],
                    ],
                ],
            ],
        ];
        $this->assertEquals($contentsAfterAdjust, $this->esContentsAdjuster->adjustContents($contentsToAdjust));
    }
}
