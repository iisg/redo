<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ContainsParentMetadataRule extends AbstractRule {

    /**
     * @param $input Metadata[]
     * @return bool
     */
    public function validate($input): bool {
        $metadataIds = array_column($input, 'baseId');
        return Validator::in($metadataIds)->validate(SystemMetadata::PARENT);
    }
}
