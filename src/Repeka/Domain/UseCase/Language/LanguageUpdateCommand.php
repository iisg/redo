<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\Command;

class LanguageUpdateCommand extends Command {
    private $languageCode;
    private $newFlag;
    private $newName;

    public function __construct(string $languageCode, string $newFlag, string $newName) {
        $this->languageCode = $languageCode;
        $this->newFlag = $newFlag;
        $this->newName = $newName;
    }

    public function getLanguageCode(): string {
        return $this->languageCode;
    }

    public function getNewFlag(): string {
        return $this->newFlag;
    }

    public function getNewName(): string {
        return $this->newName;
    }

    public static function fromArray(string $languageCode, array $data): LanguageUpdateCommand {
        return new LanguageUpdateCommand($languageCode, $data['flag'], $data['name']);
    }
}
