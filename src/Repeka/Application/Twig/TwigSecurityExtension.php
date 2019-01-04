<?php
namespace Repeka\Application\Twig;

use Repeka\Application\Security\SecurityOracle;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwigSecurityExtension extends \Twig_Extension {

    /** @var SecurityOracle */
    private $securityOracle;

    public function __construct(SecurityOracle $securityOracle) {
        $this->securityOracle = $securityOracle;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('canView', [$this, 'userCanViewResource']),
            new \Twig_Function('canViewTeaser', [$this, 'userCanViewTeaser']),
            new \Twig_Function('hasMetadataPermission', [$this, 'hasMetadataPermission']),
        ];
    }

    public function userCanViewResource(ResourceEntity $resource, ?TokenInterface $token = null) {
        return $this->hasMetadataPermission($resource, SystemMetadata::VISIBILITY(), $token);
    }

    public function userCanViewTeaser(ResourceEntity $resource, ?TokenInterface $token = null) {
        return $this->hasMetadataPermission($resource, SystemMetadata::TEASER_VISIBILITY(), $token);
    }

    public function hasMetadataPermission(ResourceEntity $resource, $metadata, ?TokenInterface $token = null): bool {
        return $this->securityOracle->hasMetadataPermission($resource, $metadata, $token);
    }
}
