<?php
namespace Repeka\Application\Serialization;

use Repeka\Application\Entity\UserEntity;
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
            'userData' => $this->normalizer->normalize($user->getUserData(), $format, $context),
            'roles' => $user->getRoles(),
        ];
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof UserEntity;
    }
}
