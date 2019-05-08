<?php
namespace Repeka\Application\Security\Voters\FileVoters;

use Repeka\Application\Security\Voters\FileDownloadVoter;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileExistsInMetadataVoter implements FileDownloadVoter {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function voteOnAccessToFile(TokenInterface $token, ResourceEntity $resource, ?string $path = null): int {
        if (is_string($path)) {
            $directoryVote = $this->pathInMetadataValues(
                MetadataControl::DIRECTORY(),
                $resource->getContents(),
                $this->getAllPossibleSubpaths($path)
            );
            $fileVote = $this->pathInMetadataValues(MetadataControl::FILE(), $resource->getContents(), [$path]);
            if (!$fileVote && !$directoryVote) {
                return VoterInterface::ACCESS_DENIED;
            }
            return VoterInterface::ACCESS_GRANTED;
        }
        return VoterInterface::ACCESS_ABSTAIN;
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
