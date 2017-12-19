<?php
namespace Repeka\Domain\XmlImport\Executor;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\XmlImport\Config\XmlImportConfig;
use Repeka\Domain\XmlImport\Expression\ConcatenationExpression;
use Repeka\Domain\XmlImport\Expression\LiteralExpression;
use Repeka\Domain\XmlImport\Expression\SubfieldExpression;
use Repeka\Domain\XmlImport\Expression\TransformExpression;
use Repeka\Domain\XmlImport\Mapping\Mapping;
use Repeka\Domain\XmlImport\Transform\JoinTransform;

class RawValueXmlImporterTest extends \PHPUnit_Framework_TestCase {
    private function metadataMock(int $id, string $name = ''): Metadata {
        /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->createMock(Metadata::class);
        $metadata->method('getBaseId')->willReturn($id);
        $metadata->method('getName')->willReturn($name);
        $metadata->expects($this->never())->method('getId');
        return $metadata;
    }

    public function testExecutesImportConfig() {
        $transforms = [
            'tildeJoin' => (new JoinTransform())->forArguments(' ~ '),
        ];
        $mappings = [
            new Mapping($this->metadataMock(1), '[tag=001]', new LiteralExpression('foo'), '1'),
            new Mapping(
                $this->metadataMock(2),
                '[tag=002]',
                new TransformExpression(new SubfieldExpression('x'), 'tildeJoin'),
                '2'
            ),
            new Mapping(
                $this->metadataMock(3),
                '[tag=003]',
                new TransformExpression(new SubfieldExpression('x'), 'tildeJoin'),
                '3'
            ),
            new Mapping($this->metadataMock(4, 'raw'), '[tag=004]', new SubfieldExpression('*'), 'raw'),
        ];
        $config = new XmlImportConfig($transforms, $mappings, ['invalid']);
        $result = (new RawValueXmlImporter())->import(<<<XML
<foo>
    <datafield tag="001"/>
    <datafield tag="002">
        <subfield code="x">A</subfield>
    </datafield>
    <datafield tag="002">
        <subfield code="x">B</subfield>
    </datafield>
    <datafield tag="003">
        <subfield code="x">X</subfield>
        <subfield code="x">Y</subfield>
        <subfield code="x">Z</subfield>
    </datafield>
    <controlfield tag="004">raw</controlfield>
</foo>
XML
            , $config);
        $this->assertEquals([
            1 => ['foo'],
            2 => ['A', 'B'],
            3 => ['X ~ Y ~ Z'],
            4 => ['raw'],
        ], $result);
    }

    public function testDetectsMissingSubfields() {
        $this->expectException(MissingSubfieldsException::class);
        $mappings = [
            new Mapping(
                $this->metadataMock(1),
                '[tag=001]',
                new ConcatenationExpression([
                    new SubfieldExpression('a'),
                    new SubfieldExpression('b'),
                    new SubfieldExpression('c'),
                    new SubfieldExpression('d'),
                ]),
                '1'
            ),
        ];
        $config = new XmlImportConfig([], $mappings, []);
        (new RawValueXmlImporter())->import(<<<XML
<foo>
    <datafield tag="001">
        <subfield code="a">foo</subfield>
        <subfield code="b">bar</subfield>
    </datafield>
</foo>
XML
            , $config);
    }
}
