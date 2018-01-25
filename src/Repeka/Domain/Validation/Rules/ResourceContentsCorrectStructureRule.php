<?php
namespace Repeka\Domain\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ResourceContentsCorrectStructureRule extends AbstractRule {
    /** @var Validator */
    private $contentsValidator;

    public function __construct() {
        $this->contentsValidator = Validator::arrayType()->each(
            Validator::arrayType()->each(
                Validator::keySet(
                    Validator::key('value'),
                    Validator::key('submetadata', Validator::arrayType(), false)
                ),
                Validator::intType()
            ),
            Validator::intType()->not(Validator::equals(0))
        );
    }

    public function validate($input) {
        return $this->contentsValidator->validate($input)
            && !in_array(false, array_map(function (array $metadataEntry) {
                return !in_array(false, array_map([$this, 'recursivelyCheckSubmetadataIfExist'], $metadataEntry));
            }, $input));
    }

    public function recursivelyCheckSubmetadataIfExist(array $metadataValue): bool {
        return isset($metadataValue['submetadata']) ? $this->validate($metadataValue['submetadata']) : true;
    }
}
