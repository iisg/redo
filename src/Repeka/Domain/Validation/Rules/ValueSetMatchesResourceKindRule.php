<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ValueSetMatchesResourceKindRule extends AbstractRule {
    private $contentsValidator;

    public function __construct(?ResourceKind $resourceKind = null) {
        if ($resourceKind) {
            $this->contentsValidator = $this->buildContentsValidator($resourceKind);
        }
    }

    private function buildContentsValidator(ResourceKind $resourceKind) {
        $metadataIdValidators = array_map(function (Metadata $metadata) {
            return Validator::key($metadata->getBaseId(), null, false);
        }, $resourceKind->getMetadataList());
        return call_user_func_array([Validator::arrayType(), 'keySet'], $metadataIdValidators);
    }

    public function forResourceKind(ResourceKind $resourceKind): ValueSetMatchesResourceKindRule {
        return new self($resourceKind);
    }

    public function validate($input) {
        Assertion::notNull($this->contentsValidator, 'You must set resource kind with ->forResourceKind($resourceKind).');
        return $this->contentsValidator->validate($input);
    }

    public function assert($input) {
        try {
            $this->contentsValidator->assert($input);
        } catch (NestedValidationException $e) {
            throw $this->reportError(array_keys($input), ['originalMessage' => $e->getFullMessage()]);
        }
    }
}
