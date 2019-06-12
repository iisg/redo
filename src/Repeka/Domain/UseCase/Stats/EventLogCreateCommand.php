<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\HttpFoundation\Request;

class EventLogCreateCommand extends AbstractCommand implements NonValidatedCommand {
    use RequireNoRoles;

    /** @var Request */
    private $request;
    /** @var string */
    private $eventName;
    /** @var ResourceEntity */
    private $resource;

    public function __construct(Request $request, string $eventName, ?ResourceEntity $resource = null) {
        $this->request = $request;
        $this->eventName = $eventName;
        $this->resource = $resource;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    public function getEventName(): string {
        return $this->eventName;
    }

    public function getResource(): ?ResourceEntity {
        return $this->resource;
    }
}
