<?php
namespace Repeka\Domain\Entity;

use Symfony\Component\HttpFoundation\Request;

class EndpointUsageLogEntry implements Identifiable {

    private $id;
    private $url;
    private $clientIP;
    private $usageDateTime;
    private $usageKey;
    private $resource;

    public function __construct(Request $request, ResourceEntity $resource, string $usageKey) {
        $this->url = $request->getUri();
        $this->clientIP = $request->getClientIp();
        $this->usageDateTime = new \DateTimeImmutable();
        $this->usageKey = $usageKey;
        $this->resource = $resource;
    }

    public function getId() {
        return $this->id;
    }
}
