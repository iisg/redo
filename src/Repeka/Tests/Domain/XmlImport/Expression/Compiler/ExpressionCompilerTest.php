<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

use Repeka\Domain\XmlImport\Expression\Subfield;
use Repeka\Domain\XmlImport\Transform\Transform;

class ExpressionCompilerTest extends \PHPUnit_Framework_TestCase {
    /** @var Transform|\PHPUnit_Framework_MockObject_MockObject */
    private $transform1;
    /** @var Transform|\PHPUnit_Framework_MockObject_MockObject */
    private $transform2;
    /** @var Transform[] */
    private $transforms;
    /** @var string[] */
    private $subfields;
    /** @var ExpressionParser|\PHPUnit_Framework_MockObject_MockObject */
    private $parser;
    /** @var ExpressionCompiler */
    private $compiler;

    protected function setUp() {
        $this->transform1 = $this->createMock(Transform::class);
        $this->transform1->method('apply')->willReturn(['one']);
        $this->transform2 = $this->createMock(Transform::class);
        $this->transform2->method('apply')->willReturn(['two']);
        $this->transforms = [
            'transform1' => $this->transform1,
            'transform2' => $this->transform2,
        ];
        $this->subfields = [new Subfield('a', 'subfield')];
        $this->parser = $this->createMock(ExpressionParser::class);
        $this->compiler = new ExpressionCompiler($this->parser);
    }

    public function testCompilingLiteralExpression() {
        $this->parser->expects($this->once())->method('parseLiteral')
            ->with("'abc'")->willReturn(new ParsingResult('literal', ''));
        $this->parser->expects($this->never())->method('parseSubfield');
        $this->parser->expects($this->never())->method('parseTransforms');
        $expr = $this->compiler->compile(["'abc'"]);
        $this->assertEquals('literal', $expr->concatenate([], []));
    }

    public function testCompilingLiteralExpressionWithTransforms() {
        $this->parser->expects($this->once())->method('parseLiteral')
            ->with("'abc'|transform1|transform2")->willReturn(new ParsingResult('literal', '|transform1|transform2'));
        $this->parser->expects($this->never())->method('parseSubfield');
        $this->parser->expects($this->once())->method('parseTransforms')
            ->with('|transform1|transform2')->willReturn(['transform1', 'transform2']);
        $this->transform1->expects($this->once())->method('apply')->with(['literal']);
        $this->transform2->expects($this->once())->method('apply')->with(['one']);
        $expr = $this->compiler->compile(["'abc'|transform1|transform2"]);
        $this->assertEquals('two', $expr->concatenate([], $this->transforms));
    }

    public function testCompilingSubfieldExpression() {
        $this->parser->expects($this->never())->method('parseLiteral');
        $this->parser->expects($this->once())->method('parseSubfield')
            ->with("a")->willReturn(new ParsingResult('a', ''));
        $this->parser->expects($this->never())->method('parseTransforms');
        $expr = $this->compiler->compile(["a"]);
        $this->assertEquals('subfield', $expr->concatenate($this->subfields, []));
    }

    public function testCompilingMultiValueSubfieldExpression() {
        $this->parser->expects($this->never())->method('parseLiteral');
        $this->parser->expects($this->once())->method('parseSubfield')
            ->with("b")->willReturn(new ParsingResult('b', ''));
        $this->parser->expects($this->never())->method('parseTransforms');
        $expr = $this->compiler->compile(["b"]);
        $this->assertEquals('B1B2', $expr->concatenate([
            new Subfield('a', 'A'),
            new Subfield('b', 'B1'),
            new Subfield('b', 'B2'),
        ], []));
    }

    public function testCompilingSubfieldExpressionWithTransforms() {
        $this->parser->expects($this->never())->method('parseLiteral');
        $this->parser->expects($this->once())->method('parseSubfield')
            ->with("a|transform2|transform1")->willReturn(new ParsingResult('a', '|transform2|transform1'));
        $this->parser->expects($this->once())->method('parseTransforms')
            ->with('|transform2|transform1')->willReturn(['transform2', 'transform1']);
        $this->transform2->expects($this->once())->method('apply')->with(['subfield']);
        $this->transform1->expects($this->once())->method('apply')->with(['two']);
        $expr = $this->compiler->compile(["a|transform2|transform1"]);
        $this->assertEquals('one', $expr->concatenate($this->subfields, $this->transforms));
    }

    public function testCompilingMultiStringExpression() {
        $this->parser->expects($this->once())->method('parseLiteral')
            ->with("'abc'")->willReturn(new ParsingResult('literal', ''));
        $this->parser->expects($this->once())->method('parseSubfield')
            ->with("a")->willReturn(new ParsingResult('a', ''));
        $this->parser->expects($this->never())->method('parseTransforms');
        $this->transform1->expects($this->never())->method('apply');
        $this->transform2->expects($this->never())->method('apply');
        $expr = $this->compiler->compile(["a", "'abc'"]);
        $this->assertEquals('subfieldliteral', $expr->concatenate($this->subfields, $this->transforms));
    }
}
