<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Respect\Validation\Rules\AbstractRule;

class MetadataGroupExistsRule extends AbstractRule {
    /** @var string[] */
    private $metadataGroupIds;

    public function __construct(array $metadataGroups) {
        $this->metadataGroupIds = array_column($metadataGroups, 'id');
    }

    /** @param $groupId string */
    public function validate($groupId) {
        return $groupId === Metadata::DEFAULT_GROUP || in_array($groupId, $this->metadataGroupIds);
    }
}
