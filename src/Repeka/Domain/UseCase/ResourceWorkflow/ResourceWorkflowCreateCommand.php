<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;

class ResourceWorkflowCreateCommand extends Command {
    private $name;

    public function __construct(array $name) {
        $this->name = $name;
    }

    public function getName(): array {
        return $this->name;
    }
}
