<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\NonValidatedCommand;

class ResourceChildrenQuery extends NonValidatedCommand {
    /** @var int|null */
    private $id;

    public function __construct(?int $id) {
        $this->id = $id;
    }

    public function getId(): ?int {
        return $this->id;
    }
}
