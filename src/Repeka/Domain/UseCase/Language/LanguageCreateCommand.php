<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\Command;

class LanguageCreateCommand extends Command {
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

    public static function fromArray(array $data): LanguageCreateCommand {
        return new LanguageCreateCommand($data ['code'], $data['flag'], $data['name']);
    }
}
