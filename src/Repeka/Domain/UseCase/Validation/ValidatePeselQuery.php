<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;

class ValidatePeselQuery extends AbstractCommand implements NonValidatedCommand {
    use RequireOperatorRole;

    private $pesel;

    public function __construct($pesel) {
        $this->pesel = $pesel;
    }

    public function getPesel() {
        return $this->pesel;
    }
}
