<?php
namespace Repeka\Plugins\Redo\Twig;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Security\SecurityOracle;
use Repeka\Application\Security\Voters\ResourceFileVoter;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;
use Repeka\Plugins\Redo\Authentication\UserDataMapping;

class TwigRedoExtension extends \Twig_Extension {
    use CurrentUserAware;

    /** @var UserDataMapping */
    private $userDataMapping;
    /** @var SecurityOracle */
    private $securityOracle;

    public function __construct(UserDataMapping $userDataMapping, SecurityOracle $securityOracle) {
        $this->userDataMapping = $userDataMapping;
        $this->securityOracle = $securityOracle;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('getUserDataMapping', [$this, 'getUserDataMapping']),
            new \Twig_Function('canUserSeeFiles', [$this, 'canUserSeeFiles']),
            new \Twig_Function('insertLinks', [$this, 'insertLinks']),
        ];
    }

    public function getUserDataMapping(): array {
        if ($this->userDataMapping->mappingExists() && $this->getCurrentUser()) {
            return $this->getUserMappedMetadataIds();
        } else {
            return [];
        }
    }

    private function getUserMappedMetadataIds() {
        return FirewallMiddleware::bypass(
            function () {
                return array_map(
                    function (Mapping $mapping) {
                        return $mapping->getMetadata()->getId();
                    },
                    $this->userDataMapping->getImportConfig()->getMappings()
                );
            }
        );
    }

    public function canUserSeeFiles(ResourceEntity $resource) {
        return $this->securityOracle->hasPermission(['resource' => $resource], ResourceFileVoter::FILE_DOWNLOAD_PERMISSION);
    }

    /**
     * Intended to use with Twig 'raw' filter.
     * Metadata value is converted using htmlspecialchars to allow both rendering HTML <a> tags and escaping original value.
     */
    public function insertLinks(MetadataValue $value, iterable $keywordMetadataList) {
        $str = $value->getValue();
        /** @var MetadataValue $keywordMetadata */
        foreach ($keywordMetadataList as $keywordMetadata) {
            $keyword = htmlspecialchars($keywordMetadata->getValue());
            /** @var MetadataValue $anySubmetadata */
            $anySubmetadata = current($keywordMetadata->getSubmetadata())[0];
            $url = $anySubmetadata->getValue();
            $link = "<a href=\"$url\">$keyword</a>";
            $str = str_replace($keyword, $link, $str);
        }
        return $value->withNewValue($str);
    }
}
