<?php
namespace Repeka\Plugins\Redo\Security\Voters\FileVoters;

use Exception;
use Repeka\Application\Security\Voters\FileDownloadVoter;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileByAddressIpVoter implements FileDownloadVoter {
    private const METADATA_PERMISSION_NAME = 'prawa_dostepu';
    private const METADATA_ADDR_IP = 'dozwolony_adres_ip';

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceRepository $resourceRepository,
        RequestStack $requestStack
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
        $this->requestStack = $requestStack;
    }

    public function voteOnAccessToFile(TokenInterface $token, ResourceEntity $resource, ?string $path = null): int {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $addrIp = $request->getClientIp();
            try {
                $allowedRightsMetadata = $this->metadataRepository->findByName(self::METADATA_PERMISSION_NAME);
                $accessRights = $resource->getContents()->getValuesWithoutSubmetadata($allowedRightsMetadata);
                if ($accessRights) {
                    return $this->accessGrantedForCurrentAddressIp($accessRights, $addrIp);
                }
            } catch (Exception $e) {
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function accessGrantedForCurrentAddressIp(array $accessRightsIds, String $currentAddrIp) {
        $addrIpMetadata = $this->metadataRepository->findByName(self::METADATA_ADDR_IP);
        foreach ($accessRightsIds as $accessRightId) {
            $allowedRightsDictionary = $this->resourceRepository->findOne($accessRightId);
            $allowedIpValues = $allowedRightsDictionary->getContents()->getValues($addrIpMetadata);
            foreach ($allowedIpValues as $allowedIp) {
                $network = $allowedIp->getValue();
                if (strpos($network, '/')) {
                    if ($this->cidrMatch($currentAddrIp, $network)) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                } else {
                    if ($network == $currentAddrIp) {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }
            }
        }
        return VoterInterface::ACCESS_DENIED;
    }

    /** @see https://stackoverflow.com/a/594134/878514 */
    public function cidrMatch($ip, $range): bool {
        list ($subnet, $bits) = explode('/', $range);
        if ($bits === null) {
            $bits = 32;
        }
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
        return ($ip & $mask) == $subnet;
    }
}
