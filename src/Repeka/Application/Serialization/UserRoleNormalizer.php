<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\UserRole;

class UserRoleNormalizer extends AbstractNormalizer {
    /**
     * @param $userRole UserRole
     * @inheritdoc
     */
    public function normalize($userRole, $format = null, array $context = []) {
        return [
            'id' => $userRole->getId(),
            'name' => $this->emptyArrayAsObject($userRole->getName()),
            'isSystemRole' => $userRole->isSystemRole(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflow;
    }
}
