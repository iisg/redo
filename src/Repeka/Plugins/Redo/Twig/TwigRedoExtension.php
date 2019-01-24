<?php
namespace Repeka\Plugins\Redo\Twig;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Service\CurrentUserAware;
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
}
