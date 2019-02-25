<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\HttpFoundation\Request;

class EndpointUsageLogCreateCommand extends AbstractCommand implements NonValidatedCommand {
    use RequireNoRoles;

    /** @var Request */
    private $request;
    /** @var string */
    private $endpointUsageTrackingKey;
    /** @var ResourceEntity */
    private $resource;

    public function __construct(Request $request, string $endpointUsageTrackingKey, ?ResourceEntity $resource = null) {
        $this->request = $request;
        $this->endpointUsageTrackingKey = $endpointUsageTrackingKey;
        $this->resource = $resource;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    public function getEndpointUsageTrackingKey(): string {
        return $this->endpointUsageTrackingKey;
    }

    public function getResource(): ?ResourceEntity {
        return $this->resource;
    }
}
