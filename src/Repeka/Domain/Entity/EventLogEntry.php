<?php
namespace Repeka\Domain\Entity;

use Symfony\Component\HttpFoundation\Request;

class EventLogEntry implements Identifiable {

    private $id;
    private $url;
    private $clientIp;
    private $eventDateTime;
    private $eventName;
    private $eventGroup;
    private $resource;

    public function __construct(string $eventName, string $eventGroup, ?ResourceEntity $resource = null, ?Request $request = null) {
        $this->eventDateTime = new \DateTimeImmutable();
        $this->eventName = $eventName;
        $this->resource = $resource;
        $this->eventGroup = $eventGroup ?: null;
        if ($request) {
            $this->url = $request->getUri();
            $this->clientIp = $request->getClientIp();
        }
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

    public function getEventDateTime(): \DateTimeImmutable {
        return $this->eventDateTime;
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
}
