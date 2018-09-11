<?php
namespace Repeka\Domain\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ResourceMetadataSortCorrectStructureRule extends AbstractRule {

    public function validate($input) {
        return Validator
            ::arrayType()->each(
                Validator::keySet(
                    Validator::key('columnId', Validator::callback([$this, 'isCorrectSortIdKey'])),
                    Validator::key(
                        'direction',
                        Validator::in(['ASC', 'DESC'])
                            ->setTemplate('sort direction is not correct')
                    ),
                    Validator::key('language', Validator::stringVal())
                )
            )->validate($input);
    }

    public function isCorrectSortIdKey(string $sortId): bool {
        return is_numeric($sortId) || in_array($sortId, ['id', 'kindId', 'label']);
    }
}
