<?php
namespace Repeka\Application\Security\Voters\FileVoters;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Application\Security\Voters\FileDownloadVoter;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileByResourceViewVoter implements FileDownloadVoter {
    /** @var SecurityOracle */
    private $securityOracle;

    public function __construct(SecurityOracle $securityOracle) {
        $this->securityOracle = $securityOracle;
    }

    public function voteOnAccessToFile(TokenInterface $token, ResourceEntity $resource, ?string $path = null): int {
        return $this->securityOracle->hasMetadataPermission($resource, SystemMetadata::VISIBILITY())
            ? VoterInterface::ACCESS_GRANTED
            : VoterInterface::ACCESS_DENIED;
    }
}
