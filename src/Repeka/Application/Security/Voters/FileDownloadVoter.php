<?php
namespace Repeka\Application\Security\Voters;

use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface FileDownloadVoter {
    const FILE_DOWNLOAD_ATTRIBUTE = 'FILE_DOWNLOAD';

    /**
     * Returns the vote for the given parameters.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param ResourceEntity $resource
     * @param string|null $path
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function voteOnAccessToFile(TokenInterface $token, ResourceEntity $resource, ?string $path = null): int;
}
