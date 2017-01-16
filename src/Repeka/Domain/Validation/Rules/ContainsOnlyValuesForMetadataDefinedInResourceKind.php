<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AbstractRule;

class ContainsOnlyValuesForMetadataDefinedInResourceKind extends AbstractRule {
    private $contentsValidator;

    public function __construct(ResourceKind $resourceKind) {
        $metadataIdValidators = array_map(function (Metadata $metadata) {
            return Validator::key($metadata->getBaseId(), null, false);
        }, $resourceKind->getMetadataList());
        $this->contentsValidator = call_user_func_array([Validator::arrayType(), 'keySet'], $metadataIdValidators);
    }

    public function validate($input) {
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
