<?php
namespace Repeka\Domain\Entity;

class AuditEntry implements Identifiable {
    private $id;
    private $commandName;
    private $successful;
    private $user;
    private $data;
    private $createdAt;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(string $commandName, ?User $user, array $data = [], bool $successful = true) {
        $this->commandName = $commandName;
        $this->user = $user;
        $this->data = $data;
        $this->successful = $successful;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId() {
        return $this->id;
    }

    public function getCommandName(): string {
        return $this->commandName;
    }

    public function getUser(): ?User {
        return $this->user;
    }

    public function getData(): array {
        return $this->data;
    }

    public function getCreatedAt(): \DateTimeInterface {
        return $this->createdAt;
    }

    public function isSuccessful(): bool {
        return $this->successful;
    }
}
