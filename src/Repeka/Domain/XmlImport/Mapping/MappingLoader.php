<?php
namespace Repeka\Domain\XmlImport\Mapping;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\XmlImport\Expression\Compiler\ExpressionCompiler;
use Repeka\Domain\XmlImport\Expression\Compiler\ExpressionCompilerException;
use Respect\Validation\Validator;
use Symfony\Component\CssSelector\CssSelectorConverter;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class MappingLoader {
    /** @var ExpressionCompiler */
    private $expressionCompiler;

    public function __construct(ExpressionCompiler $expressionCompiler) {
        $this->expressionCompiler = $expressionCompiler;
    }

    /**
     * @param array[] $mappings with ID or name string keys
     */
    public function load(array $mappings, ResourceKind $resourceKind): MappingLoaderResult {
        /** @var Metadata[] $loaded */
        $loaded = [];
        /** @var string[] $missingFromResourceKind */
        $missingFromResourceKind = [];
        foreach ($mappings as $key => $params) {
            $result = $this->loadMapping($key, $params, $resourceKind);
            if ($result !== null) {
                $loaded[] = $result;
            } else {
                $missingFromResourceKind[] = $key;
            }
        }
        return new MappingLoaderResult($loaded, $missingFromResourceKind);
    }

    private function loadMapping(string $key, array $params, ResourceKind $resourceKind): ?Mapping {
        $this->validateParams($key, $params);
        $metadata = $this->findMetadataForKey($key, $resourceKind);
        $this->validateSelector($key, $params);
        $expressionString = is_array($params['value']) ? $params['value'] : [$params['value']];
        try {
            $expression = $this->expressionCompiler->compile($expressionString);
        } catch (ExpressionCompilerException $e) {
            $e->setMappingKey($key);
            throw $e;
        }
        if ($metadata === null) {  // don't check it earlier to avoid silently ignoring invalid expressions
            return null;
        }
        return new Mapping($metadata, $params['selector'], $expression, $key);
    }

    /**
     * @param string $key
     * @return null|Metadata
     */
    private function findMetadataForKey(string $key, ResourceKind $resourceKind): ?Metadata {
        return $this->findMetadataById($key, $resourceKind) ?: $this->findMetadataByName($key, $resourceKind);
    }

    private function findMetadataById(string $baseIdString, ResourceKind $resourceKind): ?Metadata {
        if (!preg_match('/^\d+$/', $baseIdString)) {
            return null;
        }
        $baseId = intval($baseIdString);
        try {
            return $resourceKind->getMetadataById($baseId);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function findMetadataByName(string $name, ResourceKind $resourceKind): ?Metadata {
        try {
            return $resourceKind->getMetadataByName($name);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function validateParams(string $key, array $params): void {
        if (!Validator::keySet(
            Validator::key('selector', Validator::stringType()->notBlank()),
            Validator::key('value', Validator::anyOf(
                Validator::arrayType()->each(Validator::stringType()->notBlank()),
                Validator::stringType()->notBlank()
            ))
        )->validate($params)) {
            throw new InvalidMappingException($key);
        }
    }

    private function validateSelector(string $key, array $params): void {
        try {
            (new CssSelectorConverter(false))->toXPath($params['selector']);
        } catch (\Exception $e) {
            throw new InvalidSelectorException($key, $params['selector']);
        }
    }
}
