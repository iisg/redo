<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\HttpFoundation\Request;

class EventLogCreateCommand extends AbstractCommand implements NonValidatedCommand, AdjustableCommand {
    use RequireNoRoles;

    /** @var string */
    private $eventName;
    /** @var string|null */
    private $eventGroup;
    /** @var ResourceEntity */
    private $resource;
    /** @var Request */
    private $request;

    public function __construct(string $eventName, ?string $eventGroup = null, ?ResourceEntity $resource = null, ?Request $request = null) {
        $this->eventName = $eventName;
        $this->eventGroup = $eventGroup ?: 'default';
        $this->resource = $resource;
        $this->request = $request;
    }

    public function getEventName(): string {
        return $this->eventName;
    }

    public function getEventGroup(): string {
        return $this->eventGroup;
    }

    public function getResource(): ?ResourceEntity {
        return $this->resource;
    }

    public function getRequest(): ?Request {
        return $this->request;
    }
}
