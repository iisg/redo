<?php
namespace Repeka\Plugins\Redo\Security\Voters;

use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Security\Voters\ResourceFileVoter;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ResourceFileByAddressIpVoter implements VoterInterface {
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

    public function vote(TokenInterface $token, $subject, array $attributes) {
        if (in_array(ResourceFileVoter::FILE_DOWNLOAD_PERMISSION, $attributes)) {
            $user = $token->getUser();
            /** @var ResourceEntity $resource */
            $resource = $subject['resource'];
            $request = $this->requestStack->getCurrentRequest();
            $addrIp = $request->getClientIp();
            if ($user instanceof UserEntity && $resource instanceof HasResourceClass) {
                if ($user->hasRole(SystemRole::ADMIN()->roleName($resource->getResourceClass()))) {
                    return self::ACCESS_GRANTED;
                }
            }
            $allowedRightsMetadata = $this->metadataRepository->findByName(self::METADATA_PERMISSION_NAME);
            $accessRights = $resource->getContents()->getValuesWithoutSubmetadata($allowedRightsMetadata);
            if ($accessRights) {
                return $this->accessGrantedForCurrentAddressIp($accessRights, $addrIp);
            }
        }
        return self::ACCESS_ABSTAIN;
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
                        return self::ACCESS_GRANTED;
                    }
                } else {
                    if ($network == $currentAddrIp) {
                        return self::ACCESS_GRANTED;
                    }
                }
            }
        }
        return self::ACCESS_DENIED;
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
