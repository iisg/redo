<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

use Assert\Assertion;
use Repeka\Domain\XmlImport\Expression\ConcatenationExpression;
use Repeka\Domain\XmlImport\Expression\LiteralExpression;
use Repeka\Domain\XmlImport\Expression\SubfieldExpression;
use Repeka\Domain\XmlImport\Expression\TransformExpression;
use Repeka\Domain\XmlImport\Expression\ValueExpression;

class ExpressionCompiler {
    /** @var ExpressionParser */
    private $parser;

    public function __construct(ExpressionParser $parser) {
        $this->parser = $parser;
    }

    /**
     * @param string[] $expressionStrings
     */
    public function compile(array $expressionStrings): ConcatenationExpression {
        Assertion::allString($expressionStrings);
        $expressions = [];
        foreach ($expressionStrings as $string) {
            $expressions[] = $this->compileSingleString($string);
        }
        return new ConcatenationExpression($expressions);
    }

    /**
     * Expression string starts with a single-quoted literal or an unquoted single alphanumeric character which is a subfield name.
     * It can then be followed by any number of transform expressions. Transform expression is a pipe and a name of transform.
     * Transform names match /^[a-zA-Z0-9]+$/
     * Examples:
     *   'foo'
     *   a
     *   'foo'|transform1
     *   b|transform1|transform2
     */
    private function compileSingleString(string $expressionString): ValueExpression {
        /** @var string $transformsString */
        /** @var ValueExpression $startingExpression */
        if ($expressionString[0] == "'") {
            $result = $this->parser->parseLiteral($expressionString);
            $literal = $result->getParsed();
            $transformsString = $result->getRemaining();
            $startingExpression = new LiteralExpression($literal);
        } else {
            $result = $this->parser->parseSubfield($expressionString);
            $subfield = $result->getParsed();
            $transformsString = $result->getRemaining();
            $startingExpression = new SubfieldExpression($subfield);
        }
        if (mb_strlen($transformsString) === 0) {
            return $startingExpression;
        } else {
            $transformNames = $this->parser->parseTransforms($transformsString);
            /** @var ValueExpression $expression */
            $expression = $startingExpression;
            foreach ($transformNames as $transformName) {
                $expression = new TransformExpression($expression, $transformName);
            }
            return $expression;
        }
    }
}
