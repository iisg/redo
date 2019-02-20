<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Utils\StringUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResourceMetadataPermissionVoter extends Voter {
    private const METADATA_PERMISSION_PREFIX = 'METADATA_';

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    protected function supports($attribute, $subject) {
        return self::isMetadataPermission($attribute) && $subject instanceof ResourceEntity;
    }

    /**
     * @inheritdoc
     * @param ResourceEntity $resource
     */
    public function voteOnAttribute($attribute, $resource, TokenInterface $token) {
        $user = $token->getUser();
        if (!$user || !($user instanceof UserEntity)) {
            $user = SystemResource::UNAUTHENTICATED_USER()->toUser();
        }
        $metadataName = self::getMetadataNameFromPermission($attribute);
        $metadata = $this->getSystemMetadata($metadataName);
        if (!$metadata) {
            $metadata = $this->metadataRepository->findByName($metadataName);
        }
        return $resource->isUserReferencedInMetadata($user, $metadata);
    }

    /**
     * @param Metadata|SystemMetadata|string $metadata
     * @return string
     */
    public static function createMetadataPermissionName($metadata): string {
        if ($metadata instanceof SystemMetadata) {
            $metadata = $metadata->getKey();
        }
        if ($metadata instanceof Metadata) {
            $metadata = $metadata->getName();
        }
        return self::METADATA_PERMISSION_PREFIX . $metadata;
    }

    public static function isMetadataPermission(string $permissionName): bool {
        return strpos($permissionName, self::METADATA_PERMISSION_PREFIX) === 0;
    }

    public static function getMetadataNameFromPermission(string $permissionName): string {
        return StringUtils::normalizeEntityName(substr($permissionName, strlen(self::METADATA_PERMISSION_PREFIX)));
    }

    private function getSystemMetadata(string $metadataName): ?Metadata {
        switch ($metadataName) {
            case 'reproductor':
                return SystemMetadata::REPRODUCTOR()->toMetadata();
            case 'visibility':
                return SystemMetadata::VISIBILITY()->toMetadata();
            case 'teaser_visibility':
                return SystemMetadata::TEASER_VISIBILITY()->toMetadata();
            default:
                return null;
        }
    }
}
