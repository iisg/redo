<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class LanguageDeleteCommand extends AbstractCommand implements NonValidatedCommand {
    private $code;

    public function __construct(string $code) {
        $this->code = $code;
    }

    public function getCode(): string {
        return $this->code;
    }
}
