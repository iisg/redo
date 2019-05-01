<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ResourceFileVoter extends Voter {
    const FILE_DOWNLOAD_PERMISSION = 'FILE_DOWNLOAD';
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    protected function supports($attribute, $subject) {
        return $attribute === self::FILE_DOWNLOAD_PERMISSION
            && is_array($subject)
            && array_key_exists('resource', $subject)
            && $subject['resource'] instanceof ResourceEntity
            && array_key_exists('filepath', $subject)
            && is_string($subject['filepath']);
    }

    /** @inheritdoc */
    public function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        /** @var ResourceEntity $resource */
        $resource = $subject['resource'];
        $path = $subject['filepath'];
        return $this->isUserOperatorOrAdmin($resource, $token)
            || $this->pathInMetadataValues(MetadataControl::DIRECTORY(), $resource->getContents(), $this->getAllPossibleSubpaths($path))
            || $this->pathInMetadataValues(MetadataControl::FILE(), $resource->getContents(), [$path]);
    }

    private function isUserOperatorOrAdmin(ResourceEntity $resource, TokenInterface $token) {
        $user = $token->getUser();
        if ($user instanceof UserEntity
            && ($user->hasRole(SystemRole::ADMIN()->roleName($resource->getResourceClass()))
                || $user->hasRole(SystemRole::OPERATOR()->roleName($resource->getResourceClass())))) {
            return true;
        }
        return false;
    }

    private function pathInMetadataValues(MetadataControl $control, ResourceContents $contents, array $possiblePaths) {
        $metadataList = $this->metadataRepository->findByQuery(
            MetadataListQuery::builder()
                ->filterByControl($control)
                ->build()
        );
        foreach ($metadataList as $metadata) {
            $values = $contents->getValuesWithoutSubmetadata($metadata);
            if (!empty(array_intersect($possiblePaths, $values))) {
                return true;
            }
        }
        return false;
    }

    /**
     * For path "abc/def/ghi/jkl" returns ["abc/def", "abc/def/ghi", "abc/def/ghi/jkl"]
     */
    private function getAllPossibleSubpaths(string $path) {
        $pathElements = explode('/', $path);
        $pathParts = [$pathElements[0]];
        for ($i = 1; $i < count($pathElements); $i++) {
            $pathParts[] = $pathParts[$i - 1] . '/' . $pathElements[$i];
        }
        return array_splice($pathParts, 1);
    }
}
