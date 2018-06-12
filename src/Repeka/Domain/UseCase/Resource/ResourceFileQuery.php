<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceFileQuery extends ResourceClassAwareCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    /** @var ResourceEntity */
    private $resource;
    private $filename;

    public function __construct(ResourceEntity $resource, string $filename) {
        parent::__construct($resource);
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
