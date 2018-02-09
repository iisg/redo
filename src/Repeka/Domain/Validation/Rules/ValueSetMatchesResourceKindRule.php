<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Respect\Validation\Exceptions\NestedValidationException;
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
        return $this->contentsValidator->validate($input);
    }

    public function assert($input) {
        unset($input[SystemMetadata::PARENT]);
        try {
            $this->contentsValidator->assert($input);
        } catch (NestedValidationException $e) {
            throw $this->reportError(array_keys($input), ['originalMessage' => $e->getFullMessage()]);
        }
    }
}
