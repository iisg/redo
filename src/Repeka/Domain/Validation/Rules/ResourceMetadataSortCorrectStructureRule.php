<?php
namespace Repeka\Domain\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ResourceMetadataSortCorrectStructureRule extends AbstractRule {

    public function validate($input) {
        return Validator
            ::arrayType()->each(
                Validator::keySet(
                    Validator::key('metadataId', Validator::intType()),
                    Validator::key(
                        'direction',
                        Validator::in(['ASC', 'DESC'])
                            ->setTemplate('sort direction is not correct')
                    )
                )
            )->validate($input);
    }
}
