<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class MetadataGetQuery extends NonValidatedCommand {
    private $id;

    public function __construct(int $id) {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }
}
