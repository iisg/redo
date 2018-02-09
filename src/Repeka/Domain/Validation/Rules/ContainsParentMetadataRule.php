<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ContainsParentMetadataRule extends AbstractRule {
    /**
     * @param Metadata[] $input
     * @return bool
     */
    public function validate($input): bool {
        $metadataIds = EntityUtils::mapToIds($input);
        return Validator::in($metadataIds)->validate(SystemMetadata::PARENT);
    }
}
