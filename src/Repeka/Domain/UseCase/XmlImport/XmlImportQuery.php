<?php
namespace Repeka\Domain\UseCase\XmlImport;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;

class XmlImportQuery extends Command {
    /** @var string */
    private $id;
    /** @var array */
    private $config;
    /** @var ResourceKind */
    private $resourceKind;

    public function __construct(string $id, array $config, ResourceKind $resourceKind) {
        $this->id = $id;
        $this->config = $config;
        $this->resourceKind = $resourceKind;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getConfig(): array {
        return $this->config;
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }
}
