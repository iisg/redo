<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\Command;

class LanguageCreateCommand extends Command {
    private $flag;
    private $name;

    public function __construct(string $flag, string $name) {
        $this->flag = $flag;
        $this->name = $name;
    }

    public function getFlag(): string {
        return $this->flag;
    }

    public function getName(): string {
        return $this->name;
    }

    public static function fromArray(array $data): LanguageCreateCommand {
        return new LanguageCreateCommand($data['flag'], $data['name']);
    }
}
