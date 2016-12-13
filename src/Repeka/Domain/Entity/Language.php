<?php
namespace Repeka\Domain\Entity;

class Language {
    private $code;

    private $flag;

    private $name;

    public function __construct(string $code, string $flag, string $name) {
        $this->code = $code;
        $this->flag = $flag;
        $this->name = $name;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function getFlag(): string {
        return $this->flag;
    }

    public function getName(): string {
        return $this->name;
    }
}
