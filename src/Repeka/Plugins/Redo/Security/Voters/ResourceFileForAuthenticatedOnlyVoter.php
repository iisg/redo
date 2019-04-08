<?php
namespace Repeka\Plugins\Redo\Security\Voters;

use Repeka\Application\Security\Voters\ResourceFileVoter;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileForAuthenticatedOnlyVoter implements VoterInterface {
    private const METADATA_PERMISSION_NAME = 'prawa_dostepu';
    private const METADATA_CODE_NAME = 'nazwa_kodowa';
    private const ONLY_FOR_AUTH_USERS_CODENAME = 'dostep_ograniczony_uzytkownicy_zalogowani';

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceRepository $resourceRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function vote(TokenInterface $token, $subject, array $attributes) {
        if (in_array(ResourceFileVoter::FILE_DOWNLOAD_PERMISSION, $attributes)) {
            /** @var ResourceEntity $resource */
            $resource = $subject['resource'];
            $allowedRightsMetadata = $this->metadataRepository->findByName(self::METADATA_PERMISSION_NAME);
            $accessRights = $resource->getContents()->getValuesWithoutSubmetadata($allowedRightsMetadata);
            if ($accessRights) {
                $accessRights = $this->resourceRepository->findByQuery(ResourceListQuery::builder()->filterByIds($accessRights)->build());
                $codeNameMetadata = $this->metadataRepository->findByName(self::METADATA_CODE_NAME);
                $accessRightsNames = array_map(
                    function (ResourceEntity $accessRight) use ($codeNameMetadata) {
                        return $accessRight->getValuesWithoutSubmetadata($codeNameMetadata)[0] ?? null;
                    },
                    $accessRights
                );
                if (in_array(self::ONLY_FOR_AUTH_USERS_CODENAME, $accessRightsNames)) {
                    $user = $token->getUser();
                    return $user && $user->getId() > 0 ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
                }
            }
        }
        return self::ACCESS_ABSTAIN;
    }
}
