<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceKind;

class XmlImportQuery extends NonValidatedCommand {
    private $xml;
    /** @var array */
    private $config;
    /** @var ResourceKind */
    private $resourceKind;

    public function __construct(string $xml, array $config, ResourceKind $resourceKind) {
        $this->xml = $xml;
        $this->config = $config;
        $this->resourceKind = $resourceKind;
    }

    public function getXml() {
        return $this->xml;
    }

    public function getConfig(): array {
        return $this->config;
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }
}
