<?php
namespace Repeka\Domain\Entity;

use Symfony\Component\HttpFoundation\Request;

class EndpointUsageLogEntry implements Identifiable {

    private $id;
    private $url;
    private $clientIp;
    private $usageDateTime;
    private $usageKey;
    private $resource;

    public function __construct(Request $request, string $usageKey, ?ResourceEntity $resource = null) {
        $this->url = $request->getUri();
        $this->clientIp = $request->getClientIp();
        $this->usageDateTime = new \DateTimeImmutable();
        $this->usageKey = $usageKey;
        $this->resource = $resource;
    }

    public function getId() {
        return $this->id;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getClientIp(): ?string {
        return $this->clientIp;
    }

    public function getUsageDateTime(): \DateTimeImmutable {
        return $this->usageDateTime;
    }

    public function getUsageKey(): string {
        return $this->usageKey;
    }

    public function getResource(): ?ResourceEntity {
        return $this->resource;
    }
}
