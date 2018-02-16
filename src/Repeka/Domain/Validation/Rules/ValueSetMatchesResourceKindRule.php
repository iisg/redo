<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ValueSetMatchesResourceKindRule extends AbstractRule {
    /** @var Validatable */
    private $contentsValidator;

    private function buildContentsValidator(ResourceKind $resourceKind): Validatable {
        $metadataIdValidators = array_map(function (Metadata $metadata) {
            return Validator::key($metadata->getId(), null, false);
        }, $resourceKind->getMetadataList());
        return call_user_func_array([Validator::arrayType(), 'keySet'], $metadataIdValidators);
    }

    public function forResourceKind(ResourceKind $resourceKind): self {
        $instance = new self();
        $instance->contentsValidator = $this->buildContentsValidator($resourceKind);
        return $instance;
    }

    public function validate($input) {
        Assertion::notNull($this->contentsValidator, 'Resource kind not set');
        try {
            $this->assert($input);
            return true;
        } catch (ValidationException|\InvalidArgumentException $e) {
            return false;
        }
    }

    public function assert($input) {
        Assertion::notNull($this->contentsValidator, 'Resource kind not set');
        Assertion::isInstanceOf($input, ResourceContents::class);
        $contentsArray = $input->toArray();
        try {
            $this->contentsValidator->assert($contentsArray);
        } catch (NestedValidationException $e) {
            throw $this->reportError(array_keys($contentsArray), ['originalMessage' => $e->getFullMessage()]);
        }
    }
}
