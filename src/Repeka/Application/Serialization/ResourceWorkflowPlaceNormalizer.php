<?php
namespace Repeka\Application\Serialization;

use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;

class ResourceWorkflowPlaceNormalizer extends LabeledNormalizer {
    use CurrentUserAware;

    public function normalize($place, $format = null, array $context = []) {
        $normalized = parent::normalize($place, $format, $context);
        $user = $this->getCurrentUser();
        if (!$user || !$user->hasRole(SystemRole::ADMIN()->roleName())) {
            unset($normalized['pluginsConfig']);
        }
        return $normalized;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowPlace;
    }
}
