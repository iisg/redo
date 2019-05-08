<?php
namespace Repeka\Application\Security\Voters;

use Psr\Log\LoggerInterface;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CompositeFileDownloadVoter extends Voter {
    /** @var FileDownloadVoter[] */
    private $voters;
    /** @var LoggerInterface */
    private $logger;

    /** @param FileDownloadVoter[] $voters */
    public function __construct(iterable $voters, LoggerInterface $logger) {
        $this->voters = $voters;
        $this->logger = $logger;
    }

    /** @inheritdoc */
    protected function supports($attribute, $subject) {
        return FileDownloadVoter::FILE_DOWNLOAD_ATTRIBUTE == $attribute;
    }

    /** @inheritdoc */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        if (is_array($subject) && array_key_exists('resource', $subject) && $subject['resource'] instanceof ResourceEntity) {
            return $this->voteOnAccessToFile($token, $subject['resource'], $subject['filepath'] ?? null);
        }
        $this->logger->warning(
            'Invalid FILE_DOWNLOAD permission query! Expected array with subject key, given: ' . var_export($subject, true)
        );
        return false;
    }

    /**
     * Method processing file voters strategy.
     * If one of voters denies access then access to file is denied.
     * If no voter denies access and at least one of another grants access then access to file is granted.
     * If all voters abstain from voting then access to file is denied.
     *
     * @param TokenInterface $token
     * @param ResourceEntity $resource
     * @param string|null $path
     * @return bool
     */
    public function voteOnAccessToFile(TokenInterface $token, ResourceEntity $resource, ?string $path = null): bool {
        $votes = [];
        foreach ($this->voters as $voter) {
            $votes[] = $voter->voteOnAccessToFile($token, $resource, $path);
            if (in_array(VoterInterface::ACCESS_DENIED, $votes)) {
                return false;
            }
        }
        return in_array(VoterInterface::ACCESS_GRANTED, $votes);
    }
}
