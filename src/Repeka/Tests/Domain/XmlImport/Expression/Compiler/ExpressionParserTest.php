<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

use Assert\AssertionFailedException;

class ExpressionParserTest extends \PHPUnit_Framework_TestCase {
    /** @var ExpressionParser */
    private $parser;

    protected function setUp() {
        $this->parser = new ExpressionParser();
    }

    public function testParsingLiteral() {
        $result = $this->parser->parseLiteral("'abc'");
        $this->assertEquals("abc", $result->getParsed());
        $this->assertEquals('', $result->getRemaining());
        $result = $this->parser->parseLiteral("'qwerty'");
        $this->assertEquals("qwerty", $result->getParsed());
        $this->assertEquals('', $result->getRemaining());
        $result = $this->parser->parseLiteral("'qwerty'asdf");
        $this->assertEquals("qwerty", $result->getParsed());
        $this->assertEquals('asdf', $result->getRemaining());
    }

    public function testParsingEmptyLiteral() {
        $result = $this->parser->parseLiteral("''");
        $this->assertEquals('', $result->getParsed());
        $this->assertEquals('', $result->getRemaining());
    }

    public function testParsingLiteralWithEscapes() {
        $result = $this->parser->parseLiteral("'abc\\'qwe\\'jkl'");
        $this->assertEquals("abc'qwe'jkl", $result->getParsed());
        $this->assertEquals('', $result->getRemaining());
    }

    public function testParsingEmptyStringAsLiteral() {
        $this->expectException(AssertionFailedException::class);
        $this->parser->parseLiteral("");
    }

    public function testParsingInvalidStringAsLiteral() {
        $this->expectException(AssertionFailedException::class);
        $this->parser->parseLiteral('"abc"');
    }

    public function testParsingUnclosedLiteral() {
        $this->expectException(UnclosedStringLiteralException::class);
        $this->parser->parseLiteral("'qwerty");
    }

    public function testParsingUnclosedLiteralWithEscapes() {
        $this->expectException(UnclosedStringLiteralException::class);
        $this->parser->parseLiteral("'abc\\'qwe");
    }

    public function testParsingLowercaseLetterSubfield() {
        $result = $this->parser->parseSubfield("a");
        $this->assertEquals("a", $result->getParsed());
        $this->assertEquals("", $result->getRemaining());
        $result = $this->parser->parseSubfield("z");
        $this->assertEquals("z", $result->getParsed());
        $this->assertEquals("", $result->getRemaining());
        $result = $this->parser->parseSubfield("z|abc");
        $this->assertEquals("z", $result->getParsed());
        $this->assertEquals("|abc", $result->getRemaining());
    }

    public function testParsingUppercaseLetterSubfield() {
        $result = $this->parser->parseSubfield("A");
        $this->assertEquals("A", $result->getParsed());
        $this->assertEquals("", $result->getRemaining());
        $result = $this->parser->parseSubfield("Z");
        $this->assertEquals("Z", $result->getParsed());
        $this->assertEquals("", $result->getRemaining());
        $result = $this->parser->parseSubfield("Z|abc");
        $this->assertEquals("Z", $result->getParsed());
        $this->assertEquals("|abc", $result->getRemaining());
    }

    public function testParsingDigitSubfield() {
        $result = $this->parser->parseSubfield("0");
        $this->assertEquals("0", $result->getParsed());
        $this->assertEquals("", $result->getRemaining());
        $result = $this->parser->parseSubfield("9");
        $this->assertEquals("9", $result->getParsed());
        $this->assertEquals("", $result->getRemaining());
        $result = $this->parser->parseSubfield("5|abc");
        $this->assertEquals("5", $result->getParsed());
        $this->assertEquals("|abc", $result->getRemaining());
    }

    public function testParsingEmptyStringAsSubfield() {
        $this->expectException(InvalidSubfieldNameException::class);
        $this->parser->parseSubfield("");
    }

    public function testParsingMulticharSubfield() {
        $this->expectException(InvalidSubfieldNameException::class);
        $this->parser->parseSubfield("az");
    }

    public function testParsingMulticharSubfieldWithTransforms() {
        $this->expectException(InvalidSubfieldNameException::class);
        $this->parser->parseSubfield("az|transform");
    }

    public function testParsingSingleTransform() {
        $result = $this->parser->parseTransforms("|abc0ASD6");
        $this->assertEquals(['abc0ASD6'], $result);
    }

    public function testParsingMultipleTransforms() {
        $result = $this->parser->parseTransforms("|abc0ASD6|z");
        $this->assertEquals(['abc0ASD6', 'z'], $result);
    }

    public function testParsingEmptyStringAsTransform() {
        $this->expectException(AssertionFailedException::class);
        $this->parser->parseTransforms('');
    }

    public function testParsingInvalidTransformName() {
        $this->expectException(InvalidTransformNameException::class);
        $this->parser->parseTransforms("|abc|invalid name|qwe");
    }

    public function testParsingEmptyTransformName() {
        $this->expectException(InvalidTransformNameException::class);
        $this->parser->parseTransforms("|abc||qwe");
    }
}
