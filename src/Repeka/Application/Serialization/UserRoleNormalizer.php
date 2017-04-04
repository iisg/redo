<?php
namespace Repeka\Application\Serialization;

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
            'systemRoleName' => $userRole->isSystemRole() ? $userRole->toSystemRole()->getKey() : null,
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof UserRole;
    }
}
