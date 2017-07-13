<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceFileQuery extends NonValidatedCommand {
    /** @var ResourceEntity */
    private $resource;
    private $filename;

    public function __construct(ResourceEntity $resource, string $filename) {
        $this->resource = $resource;
        $this->filename = $filename;
    }

    public function getResource(): ResourceEntity {
        return $this->resource;
    }

    public function getFilename(): string {
        return $this->filename;
    }
}
