<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Respect\Validation\Rules\AbstractRule;

class TeaserVisibilityWiderOrEqualToFullVisibilityRule extends AbstractRule {

    /** @param ResourceContents $resourceContents */
    public function validate($resourceContents): bool {
        $fullVisibility = $resourceContents->getValuesWithoutSubmetadata(SystemMetadata::VISIBILITY);
        $teaserVisibility = $resourceContents->getValuesWithoutSubmetadata(SystemMetadata::TEASER_VISIBILITY);
        return array_intersect($fullVisibility, $teaserVisibility) === $fullVisibility;
    }
}
