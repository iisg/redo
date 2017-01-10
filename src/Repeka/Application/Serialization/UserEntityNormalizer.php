<?php
namespace Repeka\Application\Serialization;

use Repeka\Application\Entity\UserEntity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserEntityNormalizer implements NormalizerInterface {
    /**
     * @param $user UserEntity
     * @inheritdoc
     */
    public function normalize($user, $format = null, array $context = []) {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'staticPermissions' => $user->getStaticPermissions(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof UserEntity;
    }
}
