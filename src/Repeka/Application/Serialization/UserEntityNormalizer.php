<?php
namespace Repeka\Application\Serialization;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\UserRole;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserEntityNormalizer extends AbstractNormalizer implements NormalizerAwareInterface {
    use NormalizerAwareTrait;

    /**
     * @param $user UserEntity
     * @inheritdoc
     */
    public function normalize($user, $format = null, array $context = []) {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'userData' => $this->normalizer->normalize($user->getUserData()),
            'roles' => array_map(function (UserRole $role) {
                return $this->normalizer->normalize($role);
            }, $user->getUserRoles()),
        ];
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof UserEntity;
    }
}
