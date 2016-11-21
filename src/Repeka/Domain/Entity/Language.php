<?php
namespace Repeka\Domain\Entity;

class Language {
    private $id;

    private $flag;

    private $name;

    public function __construct(string $flag, string $name) {
        $this->flag = $flag;
        $this->name = $name;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getFlag(): string {
        return $this->flag;
    }

    public function getName(): string {
        return $this->name;
    }
}
