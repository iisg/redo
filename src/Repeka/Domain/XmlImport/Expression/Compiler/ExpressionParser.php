<?php
namespace Repeka\Domain\XmlImport\Expression\Compiler;

use Assert\Assertion;

class ExpressionParser {
    public function parseLiteral(string $expressionString): ParsingResult {
        Assertion::eq(mb_substr($expressionString, 0, 1), "'");
        $input = mb_substr($expressionString, 1);
        $literal = '';
        while (($quotePos = mb_strpos($input, "'")) !== false) {
            $betweenQuotes = mb_substr($input, 0, $quotePos);
            if (mb_substr($betweenQuotes, -1) == '\\') {
                // escaped quotation mark, continue
                $literal .= mb_substr($betweenQuotes, 0, -1) . "'";
                $input = mb_substr($input, mb_strlen($betweenQuotes) + 1);
            } else {
                // end of string
                $literal .= $betweenQuotes;
                $remaining = mb_substr($input, mb_strlen($betweenQuotes) + 1);
                return new ParsingResult($literal, $remaining);
            }
        }
        throw new UnclosedStringLiteralException($expressionString);
    }

    public function parseSubfield(string $expressionString): ParsingResult {
        if (mb_strlen($expressionString) == 1 || mb_substr($expressionString, 1, 1) == '|') {
            $subfieldName = mb_substr($expressionString, 0, 1);
            return new ParsingResult($subfieldName, mb_substr($expressionString, 1));
        } else {
            // prepare error message
            if (($pipePos = mb_strpos($expressionString, '|')) !== false) {
                $subfieldName = mb_substr($expressionString, 0, $pipePos);
            } else {
                $subfieldName = $expressionString;
            }
            throw new InvalidSubfieldNameException($subfieldName);
        }
    }

    /** @return string[] */
    public function parseTransforms(string $transformsString): array {
        Assertion::eq(mb_substr(trim($transformsString), 0, 1), '|');
        /** @var string[] $parts */
        $parts = explode('|', $transformsString);
        array_shift($parts);
        $parts = array_values(array_map('trim', $parts));
        foreach ($parts as $transformName) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $transformName)) {
                throw new InvalidTransformNameException($transformName);
            }
        }
        return $parts;
    }
}
