<?php
namespace Repeka\Plugins\Redo\Twig;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;
use Repeka\Plugins\Redo\Authentication\UserDataMapping;

class TwigRedoExtension extends \Twig_Extension {
    use CurrentUserAware;

    /** @var UserDataMapping */
    private $userDataMapping;

    public function __construct(UserDataMapping $userDataMapping) {
        $this->userDataMapping = $userDataMapping;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('getUserDataMapping', [$this, 'getUserDataMapping']),
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
